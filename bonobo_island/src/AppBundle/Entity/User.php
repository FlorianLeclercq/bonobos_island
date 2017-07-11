<?php

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany as ManyToMany;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="smallint")
     */
    protected $age = "0";
    
    /**
     * @ORM\Column(type="string")
     */
    protected $family = "Unknown";
    
    /**
     * @ORM\Column(type="string")
     */
    protected $breed = "Unknown";
    
    /**
     * @ORM\Column(type="string")
     */
    protected $favoriteFood = "Unknown";

    /**
     * @ManyToMany(targetEntity="User", mappedBy="myFriends")
     */
    protected $friendsWithMe;

    /**
     * @ManyToMany(targetEntity="User", inversedBy="friendsWithMe")
     * @ORM\JoinTable(name="myFriends",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="friend_user_id", referencedColumnName="id")}
     *      )
     */
    protected $myFriends;

    public function __construct()
    {
        parent::__construct();
        $this->friendsWithMe = new \Doctrine\Common\Collections\ArrayCollection();
        $this->myFriends = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getAge()
    {
        return $this->age;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setAge($newAge)
    {
        $this->age = $newAge;

        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getFamily()
    {
        return $this->family;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setFamily($newFamily)
    {
        $this->family = $newFamily;

        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getBreed()
    {
        return $this->breed;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setBreed($newBreed)
    {
        $this->breed = $newBreed;

        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getFavoriteFood()
    {
        return $this->favoriteFood;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setFavoriteFood($newFavoriteFood)
    {
        $this->favoriteFood = $newFavoriteFood;

        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getFriends()
    {
        return $this->myFriends;
    }
    
    /**
     * {@inheritdoc}
     * @param User $newFriend
     */
    public function addFriend(User $newFriend)
    {
        $this->myFriends->add($newFriend);

        return $this;
    }
    
    /**
     * {@inheritdoc}
     * @param User $newFriend
     */
    public function removeFriend($newFriend)
    {
        $this->myFriends->removeElement($newFriend);

        return $this;
    }
}