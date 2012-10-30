<?php
namespace Andevis\CommonBundle\Listener;

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
class RedirectorListener extends ContainerAware implements EventSubscriberInterface
{
	public function getName()
	{
		return 'andevis.event_listener.redirector_listener';
	}

	public static function getSubscribedEvents()
	{
		return array(
				KernelEvents::REQUEST => array(array('onKernelRequest')),
				);
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
