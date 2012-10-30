<?php
namespace Andevis\CommonBundle\Listener;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;

use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

use Doctrine\Common\Annotations\Reader;

use Symfony\Component\HttpKernel\KernelEvents;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JSONResponseListener implements EventSubscriberInterface
{

	/**
	 *
	 * @var Reader
	 */
	private $reader;

	/**
	 *
	 * @var Translator
	 */
	private $translator;

	public static function getSubscribedEvents()
	{
		return array(
				KernelEvents::CONTROLLER => array(array('onKernelController')),
				KernelEvents::VIEW => array(array('onKernelView')),
		);
	}

	public function getName()
	{
		return 'andevis.event_listener.json_response_listener';
	}

	/**
	 *
	 * @param Reader $reader
	 * @param RouterInterface $router
	 */
	public function __construct(Reader $reader, Translator $translator)
	{
		$this->reader = $reader;
		$this->translator = $translator;
	}

	/**
	 * Обработка аннотаций
	 * @param FilterControllerEvent $event
	 */
	public function onKernelController(FilterControllerEvent $event)
	{
		if (!is_array($controller = $event->getController())) {
			return;
		}

		$object = new \ReflectionObject($controller[0]);
		$method = $object->getMethod($controller[1]);

		$request = $event->getRequest();

		foreach($this->reader->getMethodAnnotations($method) as $annotation) {

			if ($annotation instanceof \Andevis\CommonBundle\Annotation\JSON) {
				// Processing JSON response annotation
				$request->attributes->set('_json', $annotation);
			}
		}

	}

	/**
	 * Generate JSON response
	 * @param GetResponseForControllerResultEvent $event
	 */
	public function onKernelView(GetResponseForControllerResultEvent $event)
	{
		$request = $event->getRequest();

		// Json response
		if (null !== ($annotation = $request->attributes->get('_json', null))) {
			if ($annotation instanceof \Andevis\CommonBundle\Annotation\JSON)  {
				// Get controller result
				$data = $event->getControllerResult();
				// Generate response
				$response = $annotation->generateResponse($data, $this->translator);
				$event->setResponse($response);
			}
		}
	}


}
