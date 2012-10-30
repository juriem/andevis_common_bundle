<?php
namespace Andevis\CommonBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 *
 * Запрет на исполнение в рабочем режиме.
 * Выставляется как на контролер, так и на определенный метод котнролера
 *
 * @Annotation
 * @author juriem
 *
 */
class DebugMode extends Annotation
{
	public $value = true;

	public function getDebugMode()
	{
		return $this->value;
	}
}
