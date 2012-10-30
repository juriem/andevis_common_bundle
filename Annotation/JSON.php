<?php
namespace Andevis\CommonBundle\Annotation;


use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @author juriem
 *
 */
class JSON extends Annotation
{
	/**
	 * Запрет на добавление в заголовок респоза application/json
	 * @var boolean
	 */
	public $disableContentType = false;

	/**
	 * Название ключа для статуса, возвращаемого респонза
	 * return true => {'status':'OK'}
	 * @var string
	 */
	public $statusKey = 'status';

	/**
	 * Назавание ключа, для хранения дополнительных данных
	 *
	 * return array(true, array()|any) => {'status':'OK', 'data': ...}
	 * @var unknown_type
	 */
	public $dataKey = 'data';

	/**
	 * Название ключа для хранения стринга.
	 * return 'Some string' => {'status':'OK', 'html':'Some string'}
	 * @var string
	 */
	public $stringKey = 'html';

	/**
	 * Название ключа, для хранения значения для сообщения об ошибке
	 * {'status':'ERROR', 'error':' ... '}
	 * @var string
	 */
	public $errorMessageKey = 'error';

	/**
	 * Название ключа для хранения массива сообщений об ошибках
	 * {'status':'ERROR', 'errors':[ ... ]}
	 * @var string
	 */
	public $errorMessagesKey = 'errors';

	/**
	 * Значение поля статуса в ответе - операция удалась
	 * {'status':'OK', ... }
	 * @var string
	 */
	public $statusOk = true;

	/**
	 * Значение для поля статуса в ответе - ошибка
	 * {'status':'ERROR', ...}
	 * @var string
	 */
	public $statusError = false;



	/**
	 * Генерация ответа на основании данных, пришедших из контроллера
	 * @param array|boolean|string|mixed $data
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function generateResponse($data, $translator)
	{
		$response = new \Symfony\Component\HttpFoundation\Response();
		$response->setCharset('utf-8');

		if (!$this->disableContentType) {
			$response->headers->add(array('Content-Type' => 'application/json'));
		}

		if (is_bool($data)) {
			// Обработка буленовского результата
			$jsonData = array($this->statusKey => ($data) ? $this->statusOk : $this->statusError);
		} elseif (is_array($data)) {
			// Обработка массива результата
			$value = array_shift($data);
			$skipShift = false;
			if (is_bool($value)) {
				$jsonData = array($this->statusKey => ($value ? $this->statusOk : $this->statusError));
			} else {
				$skipShift = true;
				$jsonData = array($this->statusKey => $this->statusOk);
			}

			if (!$skipShift && count($data) > 0) {
				$value = array_shift($data);
			}

			if ($jsonData[$this->statusKey] === $this->statusOk) {

				if (is_array($value)) {
					$jsonData[$this->dataKey] = $value;
				} else {
					$jsonData[$this->dataKey] = $value;
				}
			} else {
				if (is_array($value)) {
					$buffer = array();
					foreach($value as $key=>$item) {
						$item = $translator->trans($item);
						$buffer[$key] = $item;
					}
					$value = $buffer;
					$jsonData[$this->errorMessagesKey] = $value;
				} else {
					$jsonData[$this->errorMessageKey] = $translator->trans($value);
				}
			}
		} elseif (is_string($data)) {
			// Обработка стринга
			$jsonData = array($this->statusKey => $this->statusOk, $this->stringKey => $data);
		} else  {
			// Обработка всех остальных типов
			$jsonData = array($this->statusKey => $this->statusOk, $this->dataKey => $data);
		}

		$response->setContent(json_encode($jsonData));
		return $response;
	}

}
