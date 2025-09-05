<?php

namespace App\Repository;

use App\Entity\UserCurrency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserCurrency>
 *
 * @method UserCurrency|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserCurrency|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserCurrency[]    findAll()
 * @method UserCurrency[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserCurrencyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserCurrency::class);
    }

    /**
     * Pronađi ili kreira novi zapis za korisnika na osnovu IP adrese
     */
    public function findOrCreateByUserIp(string $userIp): UserCurrency
    {
        $userCurrency = $this->findOneBy(['userIp' => $userIp]);
        
        if (!$userCurrency) {
            $userCurrency = new UserCurrency();
            $userCurrency->setUserIp($userIp);
            $userCurrency->setBaseCurrency('USD'); // Default vrednost
        }
        
        return $userCurrency;
    }

    /**
     * Sačuva ili ažurira korisničke postavke
     */
    public function saveUserCurrency(UserCurrency $userCurrency): void
    {
        $userCurrency->setUpdatedAt(new \DateTime());
        $this->getEntityManager()->persist($userCurrency);
        $this->getEntityManager()->flush();
    }

    /**
     * Dohvati najnovije korisničke postavke po IP adresi
     */
    public function findLatestByUserIp(string $userIp): ?UserCurrency
    {
        return $this->createQueryBuilder('uc')
            ->andWhere('uc.userIp = :userIp')
            ->setParameter('userIp', $userIp)
            ->orderBy('uc.updatedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Obriši stare zapise (starije od 30 dana)
     */
    public function cleanupOldRecords(): int
    {
        $thirtyDaysAgo = new \DateTime('-30 days');
        
        return $this->createQueryBuilder('uc')
            ->delete()
            ->andWhere('uc.updatedAt < :thirtyDaysAgo')
            ->setParameter('thirtyDaysAgo', $thirtyDaysAgo)
            ->getQuery()
            ->execute();
    }
}