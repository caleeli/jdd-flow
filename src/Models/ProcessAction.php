<?php

namespace JDD\Workflow\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Process Actions model
 *
 * Swagger definition:
 *
 *  @OA\Schema(
 *      schema="ProcessActionEditable",
 *      @OA\Property(
 *          property="attributes",
 *          type="object",
 *          @OA\Property(property="definitions", type="string"),
 *          @OA\Property(property="data", type="object"),
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
 * @property int $token_id
 * @property string $definitions
 * @property string $element
 * @property string $event
 * @property string $name
 */
class ProcessAction extends Model
{
    protected $attributes = [
        'process_instance_id' => '',
        'process_token_id' => '',
        'definitions' => '',
        'element' => '',
        'event' => '',
        'name' => '',
    ];
    protected $fillable = [
        'process_instance_id',
        'process_token_id',
        'definitions',
        'element',
        'event',
        'name',
    ];

    public function instance()
    {
        return $this->belongsTo(ProcessInstance::class);
    }

    public function token()
    {
        return $this->belongsTo(ProcessToken::class);
    }
}
