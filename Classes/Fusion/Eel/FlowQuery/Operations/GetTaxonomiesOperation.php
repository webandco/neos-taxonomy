<?php
namespace Webandco\Taxonomy\Fusion\Eel\FlowQuery\Operations;

/*                                                                              *
 * This script belongs to the Neos Flow package "Webandco.Taxonomy".           *
 * Used to get a list of related Entries                                        *
 *                                                                              */

use Neos\ContentRepository\Exception\NodeException;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Eel\FlowQuery\Operations\AbstractOperation;
use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Log\Utility\LogEnvironment;
use Psr\Log\LoggerInterface;
use Webandco\Taxonomy\Service\Taxonomy;

/**
 * EEL intersect() operation finds cut set of nodes
 *
 */
class GetTaxonomiesOperation extends AbstractOperation {
    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $systemLogger;

    /**
     * @var Taxonomy
     * @Flow\Inject
     */
    protected $taxonomyService;

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
        $vocabularyFilter = isset($arguments[0]) && is_string($arguments[0]) ? $arguments[0] : null;

        if (!empty($nodes)) {
            /** @var NodeInterface $node */
            foreach ($nodes as $node) {
                if ($node->getProperty('taxonomies') != null) {
                    /** @var NodeInterface $t */
                    foreach ($node->getProperty('taxonomies') as $t) {
                        if (\is_string($t)) {
                            $taxonomiesNode = $node->getContext()->getNodeByIdentifier($t);
                            if (!($taxonomiesNode instanceof NodeInterface)) {
                                $this->systemLogger->error('Taxonomy was not found with identiifer: '.$t, LogEnvironment::fromMethodName(__METHOD__));
                                continue;
                            }
                            $t = $taxonomiesNode;
                        }
                        else if (!($t instanceof NodeInterface)) {
                            $this->systemLogger->error('Taxonomy is not a string and not a NodeInterface: '.\gettype($t), LogEnvironment::fromMethodName(__METHOD__));
                            $this->systemLogger->error('Logged taxonomy: '.\json_encode($t), LogEnvironment::fromMethodName(__METHOD__));
                            continue;
                        }

                        $parents = $this->getParentTaxonomies($this->taxonomyService->findDimensionVariant($t));

                        /** @var NodeInterface $vocabulary */
                        $vocabulary = end($parents);

                        if (($vocabularyFilter === null || $vocabulary->getProperty('uriPathSegment') === $vocabularyFilter) && !in_array($t, $taxonomies)) {
                            $taxonomies[] = $t;
                        }
                    }
                }
            }
        }

        $flowQuery->setContext($taxonomies);
    }

     /**
     * @param NodeInterface $taxonomy
     * @return array
     */
    protected function getParentTaxonomies(NodeInterface $taxonomy)
    {
        $parents = array();

        try {
            while (!$taxonomy->getNodeType()->isOfType('Webandco.Taxonomy:DocumentTaxonomyVocabulary')) {
                    $taxonomy = $taxonomy->findParentNode();
                    $parents[] = $taxonomy;
            }
        }catch(NodeException $exception){}

        return $parents;
    }
}
