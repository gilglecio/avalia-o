<?php

class Setting extends Model
{
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