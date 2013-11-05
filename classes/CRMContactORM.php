<?php

    class CRMContact implements IteratorAggregate
    {
        public $profile = null;
        public $createdby = null;
        public $createdtime = null;
        public $modifiedby = null;

        public function __construct( $id=null )
        {
            if ($id === NULL)
            {
                $contact = R::dispense('contact');
                $contact->setAttr('createdby', Auth::get('UserID'))
                    ->setAttr('createdtime', time())
                    ->setAttr('modifiedby', Auth::get('UserID'));
                $id = R::store($contact);
            }
            $this->contact = R::load('contact', $id);

            $profile = $this->contact->withCondition(' createdby = '.Auth::get('UserID').' ')->ownProfile;

            if ( empty($profile) )
            {
                $profile = R::dispense('profile');

                // TODO: Set other programatically generated values.
                // Issue: May fail when creating a new Contact
                //  ->setAttr('preferredname', $this->contact->firstname.' '.$this->contact->lastname)

                $profile->setAttr('createdby', Auth::get('UserID'));

                $this->contact->ownProfile = [ $profile ];

                R::store($this->contact);

                $this->profile = &$this->contact->ownProfile[ key($this->contact->ownProfile) ];
            }
            else
            {
                if (is_array($profile))
                    $this->profile = &$this->contact->ownProfile[ key($profile) ];
            }

        }

        public function getIterator()
        {
            return $this->contact->getIterator();
        }

        public function __call($name, $args)
        {
            if (method_exists($this->contact, $name))
                return call_user_func_array( [$this->contact, $name], $args);
        }

        public function __get($key)
        {
            return property_exists($this->contact, $key)
                ? $this->contact->$key
                : (property_exists($this->profile, $key)
                    ? $this->profile->$key
                    : FALSE
                );
        }

        public function loadArray($values)
        {
            $contactColumns = R::getColumns($this->contact->getMeta('type'));
            $profileColumns = R::getColumns($this->profile->getMeta('type'));

            foreach($values as $property => $value)
            {
                // TODO: Utilize field_type (INT, VARCHAR, etc) to perform validation array input
                //  $field_type = $contactColumns[$property]

                if ( array_key_exists($property, $contactColumns) )
                {
                    if ( ! property_exists($this->contact, $property) || $this->contact->$property !== $value )
                    {
                        $type_output = "\t changed from ".gettype($this->contact->$property);
                        $this->contact->$property = $value;
                    }
                }

                if ( array_key_exists($property, $profileColumns) )
                {
                    if ( ! property_exists($this->profile, $property) || $this->profile->$property !== $value )
                    {
                        $type_output = "\t changed from ".gettype($this->profile->$property);
                        $this->profile->$property = $value;
                    }

                }
            }
        }

        public function save()
        {
            R::store($this->contact);
        }

        public function delete()
        {
            R::dependencies(array('profile'=>array('contact')));

            unset($this->contact->ownProfile[$this->profile->id]);

            R::store($this->contact);
        }

    }
