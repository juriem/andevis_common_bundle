<?php
namespace Andevis\CommonBundle\Listener;

use Doctrine\Common\Annotations\AnnotationReader;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

use Symfony\Component\HttpKernel\KernelEvents;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 *
 * @author juriem
 *
 */
class BaseControllerListener implements EventSubscriberInterface, ContainerAwareInterface
{
	/**
	 *
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 *
	 * @var AnnotationReader
	 */
	private $reader;


	/**
	 *
	 * @return multitype:multitype:multitype:string number
	 */
	public static function getSubscribedEvents()
	{
		return array(
				KernelEvents::CONTROLLER => array(array('onKernelController')),
				);
	}

	public function setContainer(ContainerInterface $container = null)
	{
		$this->container = $container;
		$this->reader = $container->get('annotation_reader');
	}

	public function getName()
	{
		return 'andevis.event_listener.base_controller_listener';
	}

	/**
	 *
	 * @param FilterControllerEvent $event
	 */
	public function onKernelController(FilterControllerEvent $event)
	{
		$controller = $event->getController();

		if (!is_array($controller)) {
			return;
		}

		$controllerInstance = $controller[0];

		if ($controllerInstance instanceof \Andevis\CommonBundle\Controller\BaseController) {

			$reflection = new \ReflectionObject($controllerInstance);

			// Get properties
			$properties = $reflection->getProperties();
			foreach($properties as $property) {

				// Get annotation
				$annotations = $this->reader->getPropertyAnnotations($property);
				foreach($annotations as $annotation) {
					// Processing Injector annotation
					if ($annotation instanceof \Andevis\CommonBundle\Annotation\Injector) {

						$serviceName = $annotation->getServiceName();
						if ($this->container->has($serviceName)) {
							// Set value for property
							$property->setAccessible(true);
							$property->setValue($controllerInstance, $this->container->get($serviceName));
							$property->setAccessible(false);
						}

					}
				}
			}

		}
	}
}
