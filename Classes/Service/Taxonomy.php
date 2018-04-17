<?php
declare(strict_types=1);

namespace Webandco\Taxonomy\Service;

use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
final class Taxonomy
{

    /**
     * @var array
     * @Flow\InjectConfiguration(path="defaultDimensionPreset")
     */
    protected $defaultDimensionPreset;


    /**
     * @param NodeInterface $node
     * @return NodeInterface
     */
    public function findDimensionVariant($node)
    {

        /** @var NodeInterface $variant */
        foreach ($node->getOtherNodeVariants() as $variant) {

            $presetDimensions = $this->defaultDimensionPreset;

            if (is_array($presetDimensions)) {
                $variantDimensions = $variant->getDimensions();

                $this->recur_ksort($presetDimensions);
                $this->recur_ksort($variantDimensions);

                if (serialize($presetDimensions) == serialize($variantDimensions)) {
                    return $variant;
                }
            }
        }

        return $node;
    }

    private function recur_ksort(&$array)
    {
        foreach ($array as &$value) {
            if (is_array($value)) $this->recur_ksort($value);
        }
        return ksort($array);
    }

}
