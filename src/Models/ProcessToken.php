<?php

namespace JDD\Workflow\Models;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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
 */
class ProcessToken extends Model
{
    protected $attributes = [
        'id' => '',
        'instance_id' => '',
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
        return $this->belongsTo(Process::class);
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
}
