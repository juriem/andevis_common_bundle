<?php
namespace Andevis\CommonBundle\Controller;

use Symfony\Component\HttpFoundation\Session\Session;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Security\Core\SecurityContext;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Andevis\CommonBundle\Annotation\Injector;

/**
 * Вспомогательный класс для контроллера. Работает вместе дополнительным листенером
 * @author juriem
 *
 */
abstract class BaseController extends Controller
{

	/**
	 * @Injector("translator")
	 * @var Translator
	 */
	protected $translator;

	/**
	 * @Injector("security.context")
	 * @var SecurityContext
	 */
	protected $securityContext;

	/**
	 * @Injector("doctrine.orm.entity_manager")
	 * @var EntityManager
	 */
	protected $entityManager;

	/**
	 * @Injector("session")
	 * @var Session
	 */
	protected $session;

	/**
	 * Get repository
	 * @param string $entityName - Entity name
	 * @return \Doctrine\ORM\EntityRepository
	 */
	protected function getRepository($entityName)
	{
		return $this->entityManager->getRepository($entityName);
	}

	/*
	 * Security
	 */
	/**
	 * Проверка роли текущего пользователя
	 * @param string|array $role - Роль или массив ролей для проверки
	 * @return boolean
	 */
	protected function checkRole($role)
	{
		return $this->securityContext->isGranted($role);
	}

}
