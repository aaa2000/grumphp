<?php

namespace GrumPHP\Configuration\Compiler;

use GrumPHP\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TaskCompilerPass
 *
 * @package GrumPHP\Configuration\Compiler
 */
class TaskCompilerPass implements CompilerPassInterface
{

    const TAG_GRUMPHP_TASK = 'grumphp.task';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition('task_runner');
        $taggedServices = $container->findTaggedServiceIds(self::TAG_GRUMPHP_TASK);
        $configuration = $container->getParameter('tasks');

        $tasksRegistered = array();
        $tasksMetadata = array();
        $tasksConfiguration = array();
        foreach ($taggedServices as $id => $tags) {
            $taskTag = $this->getTaskTag($tags);
            $configKey = $taskTag['config'];
            if (in_array($configKey, $tasksRegistered)) {
                throw new RuntimeException(
                    sprintf('The name of a task should be unique. Duplicate found: %s', $configKey)
                );
            }

            $tasksRegistered[] = $configKey;
            if (!array_key_exists($configKey, $configuration)) {
                continue;
            }

            // Load configuration and metadata:
            $taskConfig = is_array($configuration[$configKey]) ? $configuration[$configKey] : array();
            $tasksMetadata[$configKey] = $this->parseTaskMetadata($taskConfig);

            // The metadata can't be part of the actual configuration.
            // This will throw exceptions during options resolving.
            unset($taskConfig['metadata']);
            $tasksConfiguration[$configKey] = $taskConfig;

            // Add the task to the task runner:
            $definition->addMethodCall('addTask', array(new Reference($id)));
        }

        $container->setParameter('grumphp.tasks.registered', $tasksRegistered);
        $container->setParameter('grumphp.tasks.configuration', $tasksConfiguration);
        $container->setParameter('grumphp.tasks.metadata', $tasksMetadata);
    }

    /**
     * @param array $tags
     *
     * @return array
     */
    private function getTaskTag(array $tags)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(array('config'));

        return $resolver->resolve(current($tags));
    }

    /**
     * @param $configuration
     *
     * @return array
     */
    private function parseTaskMetadata($configuration)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'priority' => 0,
            'blocking' => false
        ));

        $metadata = isset($configuration['metadata']) ? $configuration['metadata'] : array();

        return $resolver->resolve($metadata);
    }
}
