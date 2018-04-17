<?php

namespace Webandco\Taxonomy\Fusion\Helper;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use Webandco\Taxonomy\Service\Taxonomy;

/**
 * Listable helpers for Eel contexts
 *
 */
class TaxonomyHelper implements ProtectedContextAwareInterface
{

    /**
     * @var Taxonomy
     * @Flow\Inject
     */
    protected $taxonomyService;

    /**
     * @param array $nodes
     * @return string
     */
    public function toString($nodes)
    {

        $taxonomies = array();

        /** @var NodeInterface $node */
        foreach ($nodes as $node) {
            /** @var NodeInterface $taxonomy */
            $taxonomy  = $this->taxonomyService->findDimensionVariant($node);
            $taxonomies[] = $taxonomy->getProperty('uriPathSegment');
        }

        return implode(',', $taxonomies);
    }

    /**
     * All methods are considered safe
     *
     * @param string $methodName
     * @return boolean
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
