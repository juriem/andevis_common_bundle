<?php
namespace Andevis\CommonBundle\Extension\Twig;

use Twig_Extension;
use Twig_Filter_Method;
use Twig_Filter_Function;

class CommonTwigExtension extends Twig_Extension
{

	const DATE_FORMAT_FULL = 'datetime';
	const DATE_FORMAT_SHORT = 'date';
	const DATE_FORMAT_TIME = 'time';

	private $dateFormats = array(
			self::DATE_FORMAT_FULL => 'd.m.Y H:i',
			self::DATE_FORMAT_SHORT => 'd.m.Y',
			self::DATE_FORMAT_TIME => 'H:i'
	);

	public function getName()
	{
		return 'andevis_extension_twig';
	}

	/**
	 * (non-PHPdoc)
	 * @see Twig_Extension::getFilters()
	 */
	public function getFilters()
	{
		return array(
				'var_dump' => new \Twig_Filter_Function('var_dump'),
				'price_format' => new Twig_Filter_Method($this, 'priceFilter'),
				'date_format' => new Twig_Filter_Method($this, 'dateFilter'),
				'yes_no' => new Twig_Filter_Method($this, 'yesNo'),
				//'multiline'  => new \Twig_Filter_Function('nl2br'),
				'multiline'  =>  new Twig_Filter_Method($this, 'multiline'),
				'escapeHtml' => new \Twig_Filter_Function('htmlspecialchars'),
				'checked' => new Twig_Filter_Method($this, 'checked')
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see Twig_Extension::getFunctions()
	 */
	public function getFunctions()
	{
		return array(
				'date_now' => new \Twig_Function_Method($this, 'dateNow'),
				'begins_with' => new \Twig_Function_Method($this, 'beginsWith'),
				'var_dump' => new \Twig_Function_Function('var_dump'),
				'in_array' => new \Twig_Function_Method($this, 'inArray'),
		);
	}

	/**
	 * Функция для получения текущей даты
	 * @return \DateTime
	 */
	public function dateNow()
	{
		return new \DateTime();
	}

	/**
	 * Форматер для даты
	 * @param \DateTime $date
	 * @param string $formatType - тип формата. Допустимые значения: date, date_time, time
	 * @return string
	 */
	public function dateFilter(\DateTime $date, $formatType = 'datetime', $addSpan = false)
	{
		// Проверка существования заданного ключа
		if (key_exists($formatType, $this->dateFormats) === false) {
			throw new \Twig_Error(sprintf('Wrong value for format type <b>%s</b>. Allowed: date, datetime, time.', $formatType));
		}

		$value = $date->format($this->dateFormats[$formatType]);

		if ($addSpan) {
			echo '<span class="'.$formatType.'">'.$value.'</span>';
		} else {
			echo $value;
		}
	}

	/**
	 * Фильтр для преобразования цены
	 * @param int $number
	 * @return string
	 */
	public function priceFilter($value)
	{
		$formater = new \NumberFormatter('de-DE', \NumberFormatter::CURRENCY);
		return $formater->formatCurrency($value, 'EUR');
	}

	/**
	 * Функция begins_with(target, start).
	 * Проверяет, начинается ли строка target на строку start.
	 *
	 * @param string $target
	 * @param string $start
	 * @return boolean
	 */
	public function beginsWith($target, $start) {
		if(substr($target, 0, strlen($start)) == $start)
			return true;
		else
			return false;
	}

	/**
	 * Фильтр
	 * Преобразует булевы значения в "Да/Нет"
	 * @param bool $expression
	 * @return string
	 */
	public function yesNo($expression) {
		if($expression)
			return 'Да';
		else
			return 'Нет';
	}

	/**
	 * Функция. Определяет, находится ли элемент в массиве
	 * Примеры использования:
	 * - Установка параметра checked в чекбоксах (if in_array(item, items) => "checked", ...)
	 *
	 * @param unknown $subject
	 * @param unknown $items
	 * @return boolean
	 */
	public function inArray($subject, $items) {
		foreach($items as $item) {
			if(is_object($item) && is_object($subject)) {
				if($subject->getId() == $item->getId())
					return true;
			} else {
				if($subject == $item)
					return true;
			}
		}

		return false;
	}

	/**
	 * Фильтр. Если $expr = true, возвращает аттрибут checked.
	 * Используется для чекбоксов в форме.
	 * @param boolean $expr
	 */
	public function checked($expr) {
		if($expr)
			return ' CHECKED';
		else
			return '';
	}

	/**
	 * Фильтр. Экранирует HTML и вставляет теги <br> в многострочных полях
	 */
	public function multiline($input) {
		$str = htmlspecialchars($input);
		$str = nl2br($str);
		return $str;
	}
}
