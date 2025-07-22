<?php

namespace BddArkitect\Extension;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use BddArkitect\Context\FileStructureContext;
use BddArkitect\Context\NamespaceStructureContext;
use BddArkitect\Context\PHPClassStructureContext;

/**
 * Initializes contexts with the Arkitect configuration.
 */
class ArkitectContextInitializer implements ContextInitializer
{
    /**
     * @var ArkitectConfiguration
     */
    private ArkitectConfiguration $configuration;

    /**
     * Constructor.
     *
     * @param ArkitectConfiguration $configuration
     */
    public function __construct(ArkitectConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function initializeContext(Context $context)
    {
        // Inject the configuration into the context classes
        if ($context instanceof FileStructureContext) {
            $context->setConfiguration($this->configuration);
        }

        if ($context instanceof NamespaceStructureContext) {
            $context->setConfiguration($this->configuration);
        }

        if ($context instanceof PHPClassStructureContext) {
            $context->setConfiguration($this->configuration);
        }
    }
}
