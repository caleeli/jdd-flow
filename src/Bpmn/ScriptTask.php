<?php

namespace JDD\Workflow\Bpmn;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use JDD\Workflow\Bpmn\ScriptFormats\BaseScriptExecutor;
use JDD\Workflow\Bpmn\ScriptFormats\BashScript;
use JDD\Workflow\Bpmn\ScriptFormats\PhpScript;
use JDD\Workflow\Events\ElementConsole;
use JDD\Workflow\Models\ProcessInstance as Model;
use JDD\Workflow\Models\ProcessInstance;
use ProcessMaker\Nayra\Bpmn\Models\ScriptTask as ScriptTaskBase;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

/**
 * This activity will raise an exception when executed.
 *
 */
class ScriptTask extends ScriptTaskBase
{
    const scriptFormats = [
        'application/x-php' => PhpScript::class,
        'application/x-bash' => BashScript::class,
    ];

    private $consoleElement = null;
    /**
     * Model instance for the process instance
     *
     * @var ProcessInstance
     */
    private $model = null;

    /**
     * Runs the ScriptTask
     *
     * @param TokenInterface $token
     */
    public function runScript(TokenInterface $token)
    {
        //if the script runs correctly complete te activity, otherwise set the token to failed state
        if ($this->executeScript($token, $this->getScript(), $this->getScriptFormat())) {
            $this->complete($token);
        } else {
            $token->setStatus(ActivityInterface::TOKEN_STATE_FAILING);
        }
    }

    /**
     * Script runner fot testing purposes that just evaluates the sent php code
     *
     * @param TokenInterface $token
     * @param string $script
     *
     * @return bool
     */
    private function executeScript(TokenInterface $token, $script, $format)
    {
        $result = true;
        $logfile = $token->getId() . '.txt';
        ob_start(function ($buffer) use ($token, $logfile) {
            $this->printOutput($buffer, $token, $logfile);
        }, 1);
        try {
            Storage::disk('public')->delete($logfile);
            $this->runCode($this->model, $script, $format, $token);
        } catch (Exception $e) {
            Log::error($e->getMessage() . "\n" . $e->getTraceAsString());
            $result = false;
            $this->printOutput($e->getMessage(), $token, $logfile);
            $token->setProperty('log', $e->getMessage());
        }
        ob_end_clean();
        return $result;
    }

    /**
     * Run the code isolated
     *
     * @param Model $model
     * @param string $__filename
     *
     * @return mixed
     */
    private function runCode(ProcessInstance $model, $script, $format, Token $token)
    {
        return $this->scriptFactory($format)->run($this, $model, $script, $token);
    }

    /**
     * Create a script exector for the required $format
     *
     * @param string $format
     *
     * @return BaseScriptExecutor
     */
    private function scriptFactory($format)
    {
        $class = self::scriptFormats[$format];
        return new $class;
    }

    private function printOutput($buffer, TokenInterface $token, $logfile)
    {
        if ($this->consoleElement) {
            Storage::disk('public')->append($logfile, $buffer);
            app('events')->dispatch(new ElementConsole($token->getInstance(), $this->getConsoleElement(), [
                'url' => '/storage/' . $logfile,
            ]));
        }
    }

    /**
     * Set the bpmn element to which the console log will be sent
     *
     * @param string $element
     *
     * @return ScriptTask
     */
    public function setConsoleElement($element)
    {
        $this->consoleElement = $element;
        return $this;
    }

    /**
     * Get the console element
     *
     * @return string
     */
    public function getConsoleElement()
    {
        return $this->consoleElement;
    }

    /**
     * Set the model of the process instance
     *
     * @param \JDD\Workflow\Models\ProcessInstance $model
     *
     * @return self
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
        return $this;
    }
}
