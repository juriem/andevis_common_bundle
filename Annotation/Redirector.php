<?php
namespace Andevis\CommonBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Перенаправление на другую страницу внутри сайта, либо на другой сайт.
 * Для перенаправления на другой сайт, необходимо использовать полный путь и указать значение для external = true
 *
 * Redirector("route or url", external=true|false)
 *
 * @Annotation
 * @author juriem
 *
 */
class Redirector extends Annotation
{
	public $external = false;

	/**
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return $this->value;
	}

	/**
	 *
	 * @return boolean
	 */
	public function getExternal()
	{
		return $this->external;
	}

}
