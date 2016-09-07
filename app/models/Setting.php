<?php

class Setting extends Model
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $site_name;

    /**
     * @var string
     */
    protected $site_description;

    /**
     * @var string
     */
    protected $site_email;

    /**
     * @var string
     */
    protected $src_logo;

    /**
     * @var int
     */
    protected $status;

    public function getEmail()
    {
        return $this->site_email;
    }

    public function getName()
    {
        return $this->site_name;
    }

    public function getSrcLogo()
    {
        return $this->src_logo;
    }

    public function getDescription()
    {
        return $this->site_description;
    }

    public function getStatus()
    {
        return $this->status;
    }
}
