<?php

// Не вижу смысла разносить декоратор и декорируемый класс по разным неймспейсам, может затруднить поиск
namespace src\Decorator;

use DateTime;
use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use src\Integration\DataProvider;

// Из названия класса непонятно, что он делает
class DecoratorManager extends DataProvider
{
    // Не указаны типы свойств, затрудняет понимание
    // Свойства объявлены публичными, это несет риск несанкционированного изменения
    public $cache;
    public $logger;

    /**
     * @param string $host
     * @param string $user
     * @param string $password
     * @param CacheItemPoolInterface $cache
     */
    public function __construct($host, $user, $password, CacheItemPoolInterface $cache)
    {
        parent::__construct($host, $user, $password);
        $this->cache = $cache;
    }

    // Не вижу смысла в отдельном сеттере, так логгер можно забыть установить, рискуя получить исключение при выполнении кода
    // Лучше установить логгер в конструкторе, как остальные свойства
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    // Лучше сигнатуру метода (имя метода и параметра) привести в соответствие с методом `get` родительского класса,
    // так будет понятнее и легче для использования
    /**
     * {@inheritdoc}
     */
    public function getResponse(array $input)
    {
        try {
            $cacheKey = $this->getCacheKey($input);
            $cacheItem = $this->cache->getItem($cacheKey);
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }

            $result = parent::get($input);

            $cacheItem
                ->set($result)
                ->expiresAt(
                    (new DateTime())->modify('+1 day')
                );
            // Пропущено непосредственное сохранение данных в кэш ($this->cache->save($cacheItem))

            return $result;
        // Лучше ловить Throwable, либо специализированные типы исключений
        } catch (Exception $e) {
            $this->logger->critical('Error');
        }

        return [];
    }

    // Нет смысла делать метод публичным
    public function getCacheKey(array $input)
    {
        // Если массив большой, ключ может получиться слишком длинным
        return json_encode($input);
    }
}