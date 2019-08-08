<?php

namespace JDD\Workflow\Bpmn\ScriptFormats;

use JDD\Workflow\Bpmn\ScriptTask;
use Blade;
use Illuminate\View\Compilers\BladeCompiler;

class BashScript extends BaseScriptExecutor
{
    /**
     * Run a script code
     *
     * @param ScriptTask $scriptTask
     * @param string $script
     *
     * @return mixed
     */
    public function run(ScriptTask $scriptTask, $model, $script)
    {
        $compiler = new BladeCompiler(app('files'), config('view.compiled'));
        $compiler->setEchoFormat('escapeshellarg(%s)');
        $generated = $compiler->compileString($script);
        ob_start();
        try {
            eval('?>' . $generated);
        } catch (\Exception $e) {
            ob_get_clean();
            throw $e;
        }
        $preparedScript = ob_get_clean();
        parent::run($scriptTask, $model, $preparedScript);
    }

    /**
     * Run a file with the script code
     *
     * @param ScriptTask $scriptTask
     * @param mixed $model
     *
     * @return mixed
     */
    public function runFile(ScriptTask $scriptTask, $model)
    {
        return passthru('bash ' . escapeshellarg($this->filename) . ' 2>&1');
    }
}
