<?php
namespace Webandco\Taxonomy\Fusion\Eel\FlowQuery\Operations;

/*                                                                              *
 * This script belongs to the Neos Flow package "Webandco.Taxonomy".           *
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
class GetTaxonomiesOperation extends AbstractOperation {

    /**
     * @var \Neos\Flow\Log\SystemLoggerInterface
     * @Flow\Inject
     */
    protected $systemLogger;

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
    static protected $shortName = 'getTaxonomies';

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
        $nodes = $flowQuery->getContext();
        $taxonomies = array();
        if (!empty($nodes)) {
            /** @var Node $node */
            foreach ($nodes as $node) {
                /** @var Node $t */
                if ($node->getProperty('taxonomies') != null) {
                    foreach ($node->getProperty('taxonomies') as $t) {
                        if (!in_array($t, $taxonomies)) {
                            $this->systemLogger->log('Add Node "' . $t->getProperty('title') . '" to taxonomy list');
                            $taxonomies[] = $t;
                        }
                    }
                }
            }
        }

        $flowQuery->setContext($taxonomies);
    }
}