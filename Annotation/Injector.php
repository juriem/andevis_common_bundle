<?php
namespace Andevis\CommonBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Аннотация для встраивания сервиса. Используется на контролере.
 *
 * @Annotation
 * @author juriem
 *
 */
class Injector extends Annotation
{
	/**
	 * Получение имени сервиса
	 * @return string
	 */
	public function getServiceName()
	{
		return $this->value;
	}
}
