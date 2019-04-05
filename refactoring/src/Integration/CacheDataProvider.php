<?php

namespace src\Integration;

use DateTime;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final class CacheDataProvider implements DataProviderInterface
{
    /**
     * @var DataProviderInterface
     */
    private $dataProviderInner;

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param DataProviderInterface $dataProviderInner
     * @param CacheItemPoolInterface $cache
     * @param LoggerInterface $logger
     */
    public function __construct(
        DataProviderInterface $dataProviderInner,
        CacheItemPoolInterface $cache,
        LoggerInterface $logger
    ) {
        $this->dataProviderInner = $dataProviderInner;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $request): array
    {
        try {
            $cacheKey = $this->getCacheKey($request);
            $cacheItem = $this->cache->getItem($cacheKey);
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }

            $result = $this->dataProviderInner->get($request);

            $cacheItem
                ->set($result)
                ->expiresAt(
                    (new DateTime())->modify('+1 day')
                );
            $this->cache->save($cacheItem);

            return $result;
        } catch (Throwable $e) {
            $this->logger->critical('Error', ['exception' => $e]);
        }
    }

    private function getCacheKey(array $request): string
    {
        return hash('md5', json_encode($request));
    }
}