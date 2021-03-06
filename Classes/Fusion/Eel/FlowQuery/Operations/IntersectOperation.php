<?php
namespace Webandco\Taxonomy\Fusion\Eel\FlowQuery\Operations;

/*                                                                              *
 * This script belongs to the Neos Flow package "Webandco.Taxonomy".            *
 * Used to get a list of related Entries                                        *
 *                                                                              */

use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Eel\FlowQuery\Operations\AbstractOperation;
use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Model\Node;
use Neos\ContentRepository\Domain\Model\NodeInterface;

/**
 * EEL intersect() operation finds cut set of nodes
 *
 */
class IntersectOperation extends AbstractOperation {

    /**
     * @var \Neos\ContentRepository\Domain\Repository\NodeDataRepository
     * @Flow\Inject
     */
    protected $nodeRepository;

    /**
     * @Flow\Inject
     * @var \Neos\Neos\Service\UserService
     */
    protected $userService;

    /**
     * {@inheritdoc}
     *
     * @var string
     */
    static protected $shortName = 'intersect';

    /**
     * {@inheritdoc}
     *
     * @var integer
     */
    static protected $priority = 100;

    /**
     * {@inheritdoc}
     *
     * We can only handle NeosCR Nodes.
     *
     * @param mixed $context
     * @return boolean
     */
    public function canEvaluate($context) {
        return count($context) === 0 || (isset($context[0]) && ($context[0] instanceof NodeInterface));
    }

    /**
     * {@inheritdoc}
     *
     * @param FlowQuery $flowQuery the FlowQuery object
     * @param array $arguments the arguments for this operation
     * @return mixed
     */
    public function evaluate(FlowQuery $flowQuery, array $arguments = array())
    {

        // @todo: check if all arguments are available
        if (!isset($arguments[0]) || empty($arguments[0])) {
            throw new \Neos\Eel\FlowQuery\FlowQueryException('intersect() needs property name by which nodes should be matched', 1430401014);
        } else if (!isset($arguments[2])) {
            //return the query if the second argument is not set.
            return $flowQuery->getContext();
        } else {
            $nodes = $flowQuery->getContext();
            $mode  = $arguments[0];

            // property or node value
            $lookup = $arguments[1];

            //create array of taxonomy node path
            $taxonomies = array();
            /** @var Node $taxonomy */
            foreach (is_array($arguments[2]) ? $arguments[2]: array($arguments[2]) as $taxonomy) {
                if (!in_array($taxonomy->getNodeName(), $taxonomies)) {
                    $taxonomies[] = $taxonomy->getNodeName();
                }
            }

            // Define an output array
            $intersectedNodes = array();

            // find intersections matching nodeData properties
            if ($mode == 'property') {
                /** @var Node $node */
                foreach ($nodes as $node) {
                    //if ($node->hasProperty($lookup)) {

                        //assign nodes and check if it's an array
                        $nodesList = $node->getProperty($lookup);
                        $nodesList = is_array($nodesList) ? $nodesList : array($nodesList);

                        if ($this->containsMatchingReference($nodesList,$taxonomies)){
                            $intersectedNodes[] = $node;
                        }
                    //}
                }
            }

            // find intersections matching node properties
            // @todo: for now just "nodeType" is supported
            if ($mode == 'node' && in_array($lookup, array('nodeType'))) {
                /** @var Node $node */
                foreach ($nodes as $node) {
                    if ($this->containsMatchingReference(array($node), $taxonomies)) {
                        $intersectedNodes[] = $node;
                    }
                }
            }

            $flowQuery->setContext($intersectedNodes);
        }
    }

    /**
     * Find intersecting matches of nodes and taxonomies
     *
     * @param array $nodes array of possible nodes
     * @param array $taxonomies array of taxonomies to compare
     * @return bool matching
     */
    private function containsMatchingReference(array $nodes, array $taxonomies) {
        $nodeParts = array();

        /** @var Node $node */
        foreach ($nodes as $node) {
            $nodeParts = array_merge(explode('/', substr($node->findNodePath(), 1)), $nodeParts);
        }

        $nodeParts = array_unique($nodeParts);

        /** @var Node $taxonomy */
        foreach ($taxonomies as $taxonomy) {
           if (in_array($taxonomy, $nodeParts)) {
               return true;
           }
        }

        return false;
    }
}