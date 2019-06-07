<?php

namespace JDD\Workflow\Providers;

use App\Menu;
use App\User;
use Illuminate\Support\ServiceProvider;
use JDD\Workflow\Bpmn\Manager;
use JDD\Workflow\Facades\Workflow;
use ProcessMaker\Nayra\Storage\BpmnDocument;
use ProcessMaker\Nayra\Storage\BpmnElement;

class PackageServiceProvider extends ServiceProvider
{
    const PluginName = 'jdd/workflow';

    /**
     * If your plugin will provide any services, you can register them here.
     * See: https://laravel.com/docs/5.6/providers#the-register-method
     */
    public function register()
    {
        // Nothing is registered at this time
    }

    /**
     * After all service provider's register methods have been called, your boot method
     * will be called. You can perform any initialization code that is dependent on
     * other service providers at this time.  We've included some example behavior
     * to get you started.
     *
     * See: https://laravel.com/docs/5.6/providers#the-boot-method
     */
    public function boot()
    {
        // Workflow Engine
        $this->app->bind(
            'workflow.engine',
            function () {
                return new Manager;
            }
        );
        $this->publishes([
            __DIR__ . '/../../dist' => public_path('modules/' . self::PluginName),
            __DIR__ . '/../../config/workflow.php' => config_path('workflow.php'),
        ], self::PluginName);
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        app('config')->prepend('plugins.javascript_before', '/modules/' . self::PluginName . '/vue-jdd-flow.umd.min.js');
        app('config')->push('jsonapi.models', 'JDD\Workflow\Models');
        app('config')->push('l5-swagger.paths.annotations', __DIR__ . '/../../swagger');
        app('config')->push('l5-swagger.paths.annotations', __DIR__ . '/../Models');
        Menu::registerChildren(null, [self::class, 'workflowMenu']);
    }

    /**
     * Crea un item en el menu por cada start event de los procesos
     * registrados.
     *
     * @param array $menus
     * @param User $user
     *
     * @return array
     */
    public static function workflowMenu(array $menus, User $user)
    {
        $processes = Workflow::getProcessPaths();
        foreach ($processes as $name => $process) {
            $processUrl = $name;
            $dom = new BpmnDocument();
            $dom->load($process);
            foreach ($dom->getElementsByTagNameNS(BpmnDocument::BPMN_MODEL, 'startEvent') as $start) {
                list($tags, $text) = self::getDocumentationInfo($start);
                $menus[] = [
                    'id' => uniqid('p', true),
                    'parent' => isset($tags['menu'][0]) ? $tags['menu'][0] : null,
                    'icon' => isset($tags['icon'][0]) ? $tags['icon'][0] : '',
                    'name' => $start->getAttribute('name'),
                    'action' => sprintf(
                        'this.startProcess(%s, %s)',
                        json_encode($processUrl),
                        json_encode($start->getAttribute('id'))
                    ),
                ];
            }
        }
        return $menus;
    }

    private static function getDocumentationInfo(BpmnElement $node)
    {
        $tags = [];
        $text = '';
        $documentation = $node->getElementsByTagNameNS(BpmnDocument::BPMN_MODEL, 'documentation');
        foreach ($documentation as $doc) {
            $text .= preg_replace_callback('/@(\w+)\(([^()]+)\)/', function ($match) use (&$tags) {
                $tag = $match[1];
                $value = $match[2];
                $tags[$tag][] = $value;
                return '';
            }, $doc->textContent);
        }
        return [$tags, $text];
    }
}
