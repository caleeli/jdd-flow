<?php

namespace JDD\Workflow\Bpmn;

use Exception;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Bpmn\Models\ScriptTask as ScriptTaskBase;
use Illuminate\Support\Facades\Storage;

/**
 * This activity will raise an exception when executed.
 *
 */
class ScriptTask extends ScriptTaskBase
{
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
            ob_start(function ($buffer) {
                //Storage::append($this->getName() . '.txt', $buffer);
                error_log($buffer);
            }, 1);
            require $filename;
            ob_end_clean();
            unlink($filename);
        } catch (Exception $e) {
            $result = false;
        }
        return $result;
    }
}
