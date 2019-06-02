<?php
/**
 *  @OA\Get(
 *      tags={"jdd-workflow"},
 *      path="/api/data/process",
 *      summary="Muestra una lista de instancias de procesos",
 *      @OA\Response(
 *          response=200,
 *          @OA\JsonContent(
 *              @OA\Property(
 *                  property="data",
 *                  type="array",
 *                  @OA\Items(ref="#/components/schemas/Process"),
 *              )
 *          ),
 *          description="Lista de instancias de procesos.",
 *      )
 *  )
 *
 *  @OA\Post(
 *      tags={"jdd-workflow"},
 *      path="/api/data/process",
 *      summary="Guarda una nueva instancia de proceso",
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              @OA\Property(
 *                  property="data",
 *                  type="object",
 *                  ref="#/components/schemas/ProcessEditable"
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=201,
 *          description="success",
 *          @OA\JsonContent(ref="#/components/schemas/Process")
 *      ),
 *  )
 *
 */
