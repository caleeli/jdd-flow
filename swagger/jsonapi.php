<?php
/**
 * @OA\Get(
 *     path="/api/data/process",
 *     summary="Mostrar process",
 *     @OA\Response(
 *         response=200,
 *         description="Mostrar todos los process."
 *     ),
 *     @OA\Response(
 *         response="default",
 *         description="Lista de process."
 *     )
 * )
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
 *  ),
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
 *     @OA\Post(
 *     path="/api/data/process",
 *     summary="Guarda una nueva instancia de proceso",
 *     @OA\RequestBody(
 *       required=true,
 *       @OA\JsonContent(
 *         @OA\Property(
 *             property="data",
 *             type="object",
 *             ref="#/components/schemas/ProcessEditable"
 *         )
 *      )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="success",
 *         @OA\JsonContent(ref="#/components/schemas/Process")
 *     ),
 * )
 *
 */
