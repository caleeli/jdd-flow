<?php

namespace JDD\Workflow\Bpmn;

use Exception;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Bpmn\Models\ScriptTask as ScriptTaskBase;
use Illuminate\Support\Facades\Storage;
use JDD\Workflow\Events\ElementConsole;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use JDD\Workflow\Models\Process as Model;

/**
 * This activity will raise an exception when executed.
 *
 */
class ScriptTask extends ScriptTaskBase
{
    private $consoleElement = null;
    /**
     * Model instance for the process instance
     *
     * @var Process
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
        if ($this->executeScript($token, $this->getScript())) {
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
    private function executeScript(TokenInterface $token, $script)
    {
        $result = true;
        try {
            $filename = storage_path('app/' . uniqid('script_') . '.php');
            file_put_contents($filename, $script);
            $logfile = $token->getId() . '.txt';
            Storage::disk('public')->delete($logfile);
            ob_start(function ($buffer) use ($token, $logfile) {
                $this->printOutput($buffer, $token, $logfile);
            }, 1);
            $this->runCode($this->model, $filename);
            ob_end_clean();
            unlink($filename);
        } catch (Exception $e) {
            $result = false;
            $this->printOutput($e->getMessage(), $token, $logfile);
        }
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
    private function runCode($model, $__filename)
    {
        return require $__filename;
    }

    private function printOutput($buffer, TokenInterface $token, $logfile)
    {
        if ($this->consoleElement) {
            Storage::disk('public')->append($logfile, $buffer);
            event(new ElementConsole($token->getInstance(), $this->getConsoleElement(), [
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
     * @param \JDD\Workflow\Models\Process $model
     *
     * @return self
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
        return $this;
    }
}
