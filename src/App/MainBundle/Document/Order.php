<?php

namespace App\MainBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;
use App\MainBundle\Document\OrderContent;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @MongoDB\Document(collection="order",repositoryClass="App\Repository\OrderRepository")
 * @MongoDB\HasLifecycleCallbacks()
 */
class Order
{
    /**
     * @MongoDB\Id(type="int", strategy="INCREMENT")
     * @Groups({"details", "list"})
     * @var int
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string")
     * @Groups({"details", "list"})
     * @var string
     */
    protected $email;

    /**
     * @MongoDB\Field(type="string")
     * @Groups({"details", "list"})
     * @var string
     */
    protected $fullName;

    /**
     * @MongoDB\Field(type="string")
     * @Groups({"details", "list"})
     * @var string
     */
    protected $phone;

    /**
     * @MongoDB\Field(type="date")
     * @Groups({"details", "list"})
     * @var \DateTime
     */
    protected $createdDate;

    /**
     * @MongoDB\Field(type="date")
     * @Groups({"details", "list"})
     * @var \DateTime
     */
    protected $updatedDate;

    /**
     * @MongoDB\Field(type="string")
     * @Groups({"details", "list"})
     * @var string
     */
    protected $status;

    /**
     * @MongoDB\Field(type="string")
     * @Groups({"details", "list"})
     * @var string
     */
    protected $deliveryName;

    /**
     * @MongoDB\Field(type="float")
     * @Groups({"details", "list"})
     * @var float
     */
    protected $deliveryPrice;

    /**
     * @MongoDB\Field(type="string")
     * @Groups({"details", "list"})
     * @var string
     */
    protected $paymentName;

    /**
     * @MongoDB\Field(type="string")
     * @Groups({"details", "list"})
     * @var string
     */
    protected $paymentValue;

    /**
     * @MongoDB\Field(type="integer")
     * @Groups({"details", "list"})
     * @var int
     */
    protected $userId;

    /**
     * @MongoDB\Field(type="string")
     * @Groups({"details", "list"})
     * @var string
     */
    protected $comment;

    /**
     * @MongoDB\Field(type="string")
     * @Groups({"details", "list"})
     * @var string
     */
    protected $note;

    /**
     * @MongoDB\Field(type="float")
     * @Groups({"details", "list"})
     * @var float
     */
    protected $price;

    /**
     * @MongoDB\Field(type="string")
     * @Groups({"details", "list"})
     * @var string
     */
    protected $currency;

    /**
     * @MongoDB\Field(type="float")
     * @Groups({"details", "list"})
     * @var float
     */
    protected $currencyRate;

    /**
     * @MongoDB\Field(type="collection")
     * @Groups({"details"})
     * @var array
     */
    protected $options;

    /**
     * @MongoDB\Field(type="collection")
     * @MongoDB\EmbedMany(targetDocument="OrderContent")
     * @Groups({"details"})
     * @var array
     */
    protected $content;

    /**
     * @MongoDB\ReferenceMany(targetDocument="FileDocument", mappedBy="order", orphanRemoval=true, cascade={"all"}, storeAs="id")
     * @Groups({"details"})
     * @var array
     */
    protected $files;

    /**
     * @MongoDB\Field(type="boolean")
     * @Groups({"details", "list"})
     * @var boolean
     */
    protected $isPaid;

    /**
     * @Groups({"details", "list"})
     * @var int
     */
    protected $contentCount;

    public function __construct()
    {
        $this->content = new ArrayCollection();
        $this->files = new ArrayCollection();
    }

    /**
     * @MongoDB\PrePersist()
     */
    public function prePersist()
    {
        $this->createdDate = new \DateTime();
        $this->updatedDate = new \DateTime();
        $this->updatePriceTotal();
    }

    /**
     * @MongoDB\PreUpdate()
     */
    public function preUpdate()
    {
        $this->updatedDate = new \DateTime();
        $this->updatePriceTotal();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     * @return $this
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;
        return $this;
    }

    /**
     * Get createdDate
     *
     * @return \DateTime
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * Set updatedDate
     *
     * @param \DateTime $updatedDate
     * @return $this
     */
    public function setUpdatedDate($updatedDate)
    {
        $this->updatedDate = $updatedDate;
        return $this;
    }

    /**
     * Get updatedDate
     *
     * @return \DateTime
     */
    public function getUpdatedDate()
    {
        return $this->updatedDate;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Get userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set fullName
     *
     * @param string $fullName
     * @return $this
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
        return $this;
    }

    /**
     * Get fullName
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set deliveryName
     *
     * @param string $deliveryName
     * @return $this
     */
    public function setDeliveryName($deliveryName)
    {
        $this->deliveryName = $deliveryName;
        return $this;
    }

    /**
     * Get deliveryName
     *
     * @return string
     */
    public function getDeliveryName()
    {
        return $this->deliveryName;
    }

    /**
     * Set deliveryPrice
     *
     * @param $deliveryPrice
     * @param int $currencyRate
     * @return $this
     */
    public function setDeliveryPrice($deliveryPrice, $currencyRate = 1)
    {
        $this->deliveryPrice = round($deliveryPrice / $currencyRate, 2);
        return $this;
    }

    /**
     * Get deliveryPrice
     *
     * @return string
     */
    public function getDeliveryPrice()
    {
        return $this->deliveryPrice;
    }

    /**
     * Set paymentName
     *
     * @param string $paymentName
     * @return $this
     */
    public function setPaymentName($paymentName)
    {
        $this->paymentName = $paymentName;
        return $this;
    }

    /**
     * Get paymentName
     *
     * @return string
     */
    public function getPaymentName()
    {
        return $this->paymentName;
    }

    /**
     * Set paymentValue
     *
     * @param string $paymentValue
     * @return $this
     */
    public function setPaymentValue($paymentValue)
    {
        $this->paymentValue = $paymentValue;
        return $this;
    }

    /**
     * Get paymentValue
     *
     * @return string
     */
    public function getPaymentValue()
    {
        return $this->paymentValue;
    }

    /**
     * Set note
     *
     * @param string $note
     * @return $this
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * Get note
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @return int
     */
    public function getContentCount()
    {
        $countTotal = 0;
        $contentArr = $this->getContent();
        /** @var OrderContent $content */
        foreach ($contentArr as $content) {
            $countTotal += $content->getCount();
        }
        return $countTotal;
    }

    /**
     * @return array
     */
    public function toArray($full = false)
    {
        $output = [
            'id' => $this->getId(),
            'userId' => $this->getUserId(),
            'price' => $this->getPrice(),
            'email' => $this->getEmail(),
            'phone' => $this->getPhone(),
            'fullName' => $this->getFullName(),
            'createdDate' => $this->getCreatedDate(),
            'updatedDate' => $this->getUpdatedDate(),
            'deliveryName' => $this->getDeliveryName(),
            'deliveryPrice' => $this->getDeliveryPrice(),
            'paymentName' => $this->getPaymentName(),
            'deliveryValue' => $this->getPaymentValue(),
            'comment' => $this->getComment(),
            'status' => $this->getStatus(),
            'contentCount' => $this->getContentCount(),
            'currency' => $this->getCurrency()
        ];
        if($full){
            $output = array_merge($output, [
                'content' => $this->getContentArray(),
                'options' => $this->getOptions()
            ]);
        }
        return $output;
    }

    /**
     * @param $shopCartData
     * @param $filesCollection
     * @param int $currencyRate
     * @return $this
     */
    public function setContentFromCart($shopCartData, $filesCollection, $currencyRate = 1)
    {
        foreach ($shopCartData['data'] as $contentTypeName => $products) {
            foreach ($products as $product) {
                $uri = $product['parentUri'] . $product['systemName'];
                $orderContent = new OrderContent();
                $files = isset($product['files']) ? $product['files'] : [];
                $orderContent
                    ->setId($product['id'])
                    ->setTitle($product['title'])
                    ->setCount($product['count'])
                    ->setPrice($product['price'], $currencyRate)
                    ->setImage($product['image'])
                    ->setUri($uri)
                    ->setContentTypeName($contentTypeName)
                    ->setParameters($product['parameters'], $currencyRate);

                if (!empty($files)) {
                    foreach ($files as $fileData) {
                        $fileDocuments = $filesCollection->filter(function($entry) use ($fileData) {
                            return $entry->getId() === $fileData['fileId'];
                        });
                        if (!empty($fileDocuments)) {
                            /** @var FileDocument $fileDocument */
                            $fileDocument = $fileDocuments->current();
                            $fileDocument
                                ->setOrder($this)
                                ->setOwnerType(FileDocument::OWNER_ORDER_PRODUCT)
                                ->setOwnerId(0);

                            $orderContent->addFile($fileDocument);//Add array data
                            $this->addFile($fileDocument);
                        }
                    }
                }

                $this->addContent($orderContent);
            }
        }
        return $this;
    }

    /**
     * Set price
     *
     * @param $price
     * @param int $currencyRate
     * @return $this
     */
    public function setPrice($price, $currencyRate = 1)
    {
        $this->price = round($price / $currencyRate, 2);
        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return $this
     */
    public function updatePriceTotal()
    {
        $priceTotal = 0;
        /** @var OrderContent $content */
        foreach ($this->content as $content) {
            $priceTotal += $content->getPriceTotal();
        }
        if ($this->deliveryPrice) {
            $priceTotal += $this->deliveryPrice;
        }
        $this->price = $priceTotal;
        return $this;
    }

    /**
     * Set currency
     *
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set currency rate
     *
     * @param float $currencyRate
     * @return $this
     */
    public function setCurrencyRate($currencyRate)
    {
        $this->currencyRate = $currencyRate;
        return $this;
    }

    /**
     * Get currency rate
     *
     * @return float
     */
    public function getCurrencyRate()
    {
        return $this->currencyRate;
    }

    /**
     * Set options
     *
     * @param array $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get content
     *
     * @return ArrayCollection
     */
    public function getContent()
    {
        return $this->content;
    }

    public function getContentArray()
    {
        $output = [];
        /** @var OrderContent $content */
        foreach ($this->content as $content) {
            $output[] = $content->toArray();
        }
        return $output;
    }

    /**
     * Set content
     *
     * @param ArrayCollection $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Add content
     *
     * @param OrderContent $content
     */
    public function addContent(OrderContent $content)
    {
        $this->content->add($content);
    }

    /**
     * Remove content
     *
     * @param OrderContent $content
     */
    public function removeContent(OrderContent $content)
    {
        $this->content->removeElement($content);
    }

    /**
     * Add file
     *
     * @param FileDocument $file
     * @return $this
     */
    public function addFile(FileDocument $file)
    {
        $this->files->add($file);
        return $this;
    }

    /**
     * Remove file
     *
     * @param FileDocument $file
     * @return $this
     */
    public function removeFile(FileDocument $file)
    {
        $this->files->removeElement($file);
        return $this;
    }

    /**
     * Get files
     *
     * @return ArrayCollection
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Set isPaid
     *
     * @param boolean $isPaid
     * @return $this
     */
    public function setIsPaid($isPaid)
    {
        $this->isPaid = $isPaid;
        return $this;
    }

    /**
     * Get isPaid
     *
     * @return boolean
     */
    public function getIsPaid()
    {
        return $this->isPaid;
    }

    /**
     * Get status color
     * @param $orderStatusSettings
     * @return string
     */
    public function getStatusColor($orderStatusSettings)
    {
        $color = '';
        $orderStatus = $this->getStatus();
        $statusSetting = array_filter($orderStatusSettings, function($setting) use ($orderStatus) {
            /** @var Setting $setting */
            return $setting->getName() == $orderStatus;
        });
        if (!empty($statusSetting)) {
            $statusSetting = current($statusSetting);
            /** @var Setting $statusSetting */
            $settingOptions = $statusSetting->getOptions();

            return $settingOptions['color']['value'];
        }
        return $color;
    }

    /**
     * @param int $tax
     * @param string $unit
     * @return array
     */
    public function getReceipt($tax = 3, $unit = '')
    {
        $receipt = [];
        /** @var OrderContent $content */
        foreach ($this->getContent() as $content) {
            $receipt[] = [
                'quantity' => $content->getCount(),
                'price' => [
                    'amount' => number_format($content->getPriceTotal(), 2, '.', '')
                ],
                'tax' => $tax,
                'text' => $content->getTitle() . ($unit ? ", {$unit}" : $unit)
            ];
        }
        if ($this->getDeliveryPrice() > 0) {
            $receipt[] = [
                'quantity' => 1,
                'price' => [
                    'amount' => number_format($this->getDeliveryPrice(), 2, '.', '')
                ],
                'tax' => 1,
                'text' => $this->getDeliveryName()
            ];
        }

        return $receipt;
    }

}
