<?php

namespace JDD\Workflow\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use JDD\Workflow\Facades\Workflow;

/**
 * Process model
 *
 * Swagger definition:
 *
 *  @OA\Schema(
 *      schema="ProcessEditable",
 *      @OA\Property(
 *          property="attributes",
 *          type="object",
 *          @OA\Property(property="bpmn", type="string"),
 *          @OA\Property(property="data", type="object"),
 *          @OA\Property(
 *              property="tokens",
 *              type="array",
 *              @OA\Items(
 *                  type="object",
 *                  @OA\Property(property="element", type="string"),
 *                  @OA\Property(property="name", type="string"),
 *                  @OA\Property(property="implementation", type="string"),
 *                  @OA\Property(property="status", type="string"),
 *              )
 *          ),
 *          @OA\Property(property="status", type="string", enum={"ACTIVE", "COMPLETED"}),
 *      )
 *  )
 *
 *  @OA\Schema(
 *      schema="Process",
 *      allOf={
 *          @OA\Schema(
 *              @OA\Property(property="id", type="string", format="id"),
 *          ),
 *          @OA\Schema(ref="#/components/schemas/ProcessEditable"),
 *          @OA\Schema(
 *              @OA\Property(
 *                  property="attributes",
 *                  type="object",
 *                  @OA\Property(property="created_at", type="string", format="date-time"),
 *                  @OA\Property(property="updated_at", type="string", format="date-time"),
 *                  @OA\Property(property="id", type="string", format="id"),
 *              )
 *          )
 *      }
 *  )
 * @property int $id
 * @property int $instance_id
 * @property string $definitions
 * @property string $element
 * @property string $status
 * @property string $name
 * @property string $implementation
 * @property int $index
 * @property string $log
 */
class ProcessToken extends Model
{
    protected $attributes = [
        'id' => '',
        'instance_id' => '',
        'definitions' => '',
        'element' => '',
        'status' => '',
        'name' => '',
        'implementation' => '',
        'index' => 0,
        'log' => '',
    ];
    protected $fillable = [];

    public function instance()
    {
        return $this->belongsTo(ProcessInstance::class);
    }

    public function scopeWhereActiveIn($query, array $ids)
    {
        return $query->where(function ($query) use ($ids) {
            foreach ($ids as $id) {
                $query->orWhere(function ($query) use ($id) {
                    $query->where('element', $id);
                    $query->where('status', 'ACTIVE');
                });
            }
        });
    }

    /**
     * Filter for user logged
     *
     * @param mixed $query
     *
     * @return mixed
     */
    public function scopeWhereUserLogged($query)
    {
        return $query->where('user_id', Auth::id());
    }

    /**
     * Complete a task of the process instance
     *
     * @param array $data
     *
     * @return array
     */
    public function complete(array $data = [])
    {
        Workflow::completeTask($this->instance_id, $this->id, $data);
        return [
            'id' => $this->getKey()
        ];
    }

    /**
     * Update data from a token
     *
     * @param array $data
     *
     * @return array
     */
    public function updateData(array $data = [])
    {
        Workflow::updateData($this->instance_id, $this->id, $data);
        return [
            'id' => $this->getKey()
        ];
    }

    /**
     * Get process instance data through process token
     *
     * @param array $variables
     *
     * @return object
     */
    public function getData($variables = ['*'], $default = [])
    {
        $default = (array) $default;
        $data = [];
        foreach ($variables as $variable) {
            if ($variable === '*') {
                return (object) $this->instance->data;
            } else {
                $data[$variable] = $this->instance->data->$variable ?? $default[$variable] ?? null;
            }
        }
        return (object) $data;
    }

    public function scopeDoCount($query)
    {
        $query = $query->select([DB::raw('count(*) as count')]);
        return $query;
    }

    /**
     * Get screen for the token
     *
     * @return string
     */
    public function getScreen()
    {
        $element = Workflow::getElementById($this->definitions, $this->element);
        $implementation = $element->getProperty('implementation');
        if ($implementation && substr($implementation, 0, 2) === './') {
            return $this->loadScreenFromFile($implementation);
        }
        if ($implementation) {
            return $implementation;
        }
        // Load from documentation
        $nodes = $element->getBpmnElement()->getElementsByTagName("documentation");
        foreach ($nodes as $node) {
            switch ($node->getAttribute('textFormat')) {
                case 'text/plain':
                    return trim($node->nodeValue);
                case 'text/url':
                    $file = trim($node->nodeValue);
                    return $this->loadScreenFromFile($file);
            }
        }
        return '';
    }

    private function loadScreenFromFile($file)
    {
        $path = dirname(Workflow::getProcessPath($this->definitions));
        if (substr($file, 0, 2) !== './') {
            throw new InvalidArgumentException('Invalid screen resource, should be of type ./filepath.vue');
        }
        $filepath = $path . '/' . basename($file);
        if (!file_exists($filepath)) {
            throw new InvalidArgumentException('Screen resource not found. ['. $file.']');
        }
        return file_get_contents($filepath);
    }
}
