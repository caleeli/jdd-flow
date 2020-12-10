<?php

namespace JDD\Workflow\Bpmn;

use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
use ProcessMaker\Nayra\Bpmn\FormalExpressionTrait;
use ProcessMaker\Nayra\Bpmn\Models\DataStore;
use ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * FormalExpression implementation
 *
 */
class FormalExpression implements FormalExpressionInterface
{

    use FormalExpressionTrait;

    /**
     * Get the body of the Expression.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->getProperty(FormalExpressionInterface::BPMN_PROPERTY_BODY);
    }

    /**
     * Get the type that this Expression returns when evaluated.
     *
     * @return string
     */
    public function getEvaluatesToType()
    {
        return $this->getProperty(FormalExpressionInterface::BPMN_PROPERTY_EVALUATES_TO_TYPE_REF);
    }

    /**
     * Get the expression language.
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->getProperty(FormalExpressionInterface::BPMN_PROPERTY_LANGUAGE);
    }

    /**
     * Invoke the format expression.
     *
     * @param mixed $data
     *
     * @return string
     */
    public function __invoke($data, TokenInterface $token)
    {
        extract((array) $data);
        $data = (object) $data;
        $instance = $token->getInstance();
        return $this->getDateExpression()
            ?: $this->getCycleExpression()
            ?: $this->getDurationExpression()
            ?: eval((substr($this->getBody(), 0, 5) === '<?php' ? '?>' : 'return ') . $this->getBody() . ';');
    }

    /**
     * Verify if the expression is a date.
     *
     * @return boolean
     */
    private function isDateExpression()
    {
        $expression = $this->getProperty(FormalExpressionInterface::BPMN_PROPERTY_BODY);
        try {
            $date = new DateTime($expression);
        } catch (Exception $e) {
            return false;
        }
        return $date !== false;
    }

    /**
     * Verify if the expression is a cycle.
     *
     * @return boolean
     */
    private function isCycleExpression()
    {
        $expression = $this->getProperty(FormalExpressionInterface::BPMN_PROPERTY_BODY);
        try {
            $cycle = new DatePeriod($expression);
        } catch (Exception $e) {
            return false;
        }
        return $cycle !== false;
    }

    /**
     * Verify if the expression is a duration.
     *
     * @return boolean
     */
    private function isDurationExpression()
    {
        $expression = $this->getProperty(FormalExpressionInterface::BPMN_PROPERTY_BODY);
        try {
            $interval = new DateInterval($expression);
        } catch (Exception $e) {
            return false;
        }
        return $interval !== false;
    }
}
