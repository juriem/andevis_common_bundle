<?php
namespace Andevis\CommonBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\Annotations\FileCacheReader;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;

use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Routing\RouterInterface;

use Symfony\Component\HttpKernel\EventListener\RouterListener;

use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

use Doctrine\Common\Annotations\Reader;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

use Symfony\Bundle\FrameworkBundle\Routing\Router;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

use Symfony\Component\HttpKernel\KernelEvents;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener for redirecto annotation
 * @author juriem
 *
 */
class RedirectorListener implements EventSubscriberInterface, ContainerAwareInterface
{

	/**
	 *
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 *
	 * @var Reader
	 */
	protected $reader;

	/**
	 * (non-PHPdoc)
	 * @see \Symfony\Component\DependencyInjection\ContainerAware::setContainer()
	 */
	public function setContainer(ContainerInterface $container = null)
	{
		$this->container = $container;

	}

	public function getName()
	{
		return 'andevis.event_listener.redirector_listener';
	}

	public static function getSubscribedEvents()
	{
		return array(
				KernelEvents::CONTROLLER => array(array('onKernelController')),
				KernelEvents::VIEW => array(array('onKernelView')),
				//KernelEvents::REQUEST => array(array('onKernelRequest', 0)),
				);
	}

	public function onKernelController(FilterControllerEvent $event)
	{
		if (!is_array($controller = $event->getController())) {
			return;
		}

		$object = new \ReflectionObject($controller[0]);
		$method = $object->getMethod($controller[1]);

		$objectReflection = new \ReflectionObject($object);

		//$classAnnotations = $this->container->get('annotation_reader')->getClassAnnotation($objectReflection);

		// Processing global annotations
		/*$classAnnotations = $this->container->get('annotation_reader')->getClassAnnotations($objectReflection);
		foreach($classAnnotations as $annotation) {
			if ($this->processAnnotation($annotation, $event)) {

				return;
			}
		}*/


		// Processing method annotations
		$methodAnnotations = $this->container->get('annotation_reader')->getMethodAnnotations($method);

		foreach($methodAnnotations as $annotation) {

			if ($annotation instanceof \Andevis\CommonBundle\Annotation\Redirector){
				$event->getRequest()->attributes->set('_redirector', $annotation);
			}
		}
	}

	/**
	 *
	 * @param GetResponseForControllerResultEvent $event
	 */
	public function onKernelView(GetResponseForControllerResultEvent $event)
	{
		$request = $event->getRequest();

		// Json response
		if (null !== ($annotation = $request->attributes->get('_redirector', null))) {
			if ($annotation instanceof \Andevis\CommonBundle\Annotation\Redirector)  {

				if ($annotation->getExternal()) {
					$url = $annotation->getUrl();
				} else {
					$url = $this->container->get('router')->generate($annotation->getUrl());
				}
				$event->setResponse(new RedirectResponse($url));
			}
		}
	}




	public function onKernelRequest(GetResponseEvent $event)
	{

		/*
		 * Get information about controller and method
		 */
		try {
			$params = $this->container->get('router')->match($event->getRequest()->getPathInfo());

		} catch(ResourceNotFoundException $e) {
			return;
		}

		/*
		 * Processing controller information: class and method
		 */
		if (preg_match('/(.*)::(.*)/si', $params['_controller'], $matches)) {
			$controllerClass = $matches[1];
			$methodName = $matches[2];
		} else {
			return;
		}

		// Get reflection for controller class
		$classReflection = new \ReflectionClass($controllerClass);

		// Processing global annotations
		$classAnnotations = $this->container->get('annotation_reader')->getClassAnnotations($classReflection);
		foreach($classAnnotations as $annotation) {
			if ($this->processAnnotation($annotation, $event)) {

				return;
			}
		}


		// Processing method annotations
		$methodAnnotations = $this->container->get('annotation_reader')->getMethodAnnotations($classReflection->getMethod($methodName));

		foreach($methodAnnotations as $annotation) {
			if ($this->processAnnotation($annotation, $event)) {

				return;
			}
		}
	}




	/**
	 * Processing annotation
	 * @param object $annotation
	 * @param object $event
	 * @return boolean
	 */
	private function processAnnotation($annotation, $event)
	{

		/*
		 * Processing debug mode annotation
		 */
		if ($annotation instanceof \Andevis\CommonBundle\Annotation\DebugMode) {

			if ($annotation->getDebugMode() === true && $this->container->get('kernel')->isDebug() !== true) {
				throw new \Exception('Not allowed in debug mode!');
			}
		}

		/*
		 * Processing redirector annotation
		 */
		if ($annotation instanceof \Andevis\CommonBundle\Annotation\Redirector) {
			// Processing redirect
			if ($annotation->getExternal()) {
				$url = $annotation->getUrl();
			} else {
				$url = $this->container->get('router')->generate($annotation->getUrl());
			}
			$event->setResponse(new RedirectResponse($url));

			return true;
		}

		return false;
	}
}
