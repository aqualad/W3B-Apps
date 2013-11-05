<?php

    class CRMContact {

        public  $id         = null,
                $createdby  = null,
                $firstname  = null,
                $lastname   = null,
                $email      = null,
                $company    = null,
                $profession = null,
                $phone      = null,
                $mobile     = null,
                $fax        = null,
                $url        = null,
                $address    = null,
                $address2   = null,
                $city       = null,
                $state      = null,
                $zip        = null,
                $country    = null;

        // [ ColumnNames  =>  InitialValueFromDB ]
        public $record = null;

        public $fieldid = null;

        public $createdtime = null;


        public function __construct( $contact_id=null, $creator_uid=null, $attributes=null )
        {
            $this->id = $contact_id ?: null;

            $this->createdby = $creator_uid ?: Auth :: get('UserID');

            if( is_numeric($this->id) )
            {
                $this->load();
            }
            else
            {
                $this->record = array_fill_keys( array_keys(self::fields()), null );

                if ( ! is_null($attributes) && is_array($attributes) )
                {
                    foreach($attributes as $key => $value)
                    {
                        $this->$key = $value;
                    }
                }
            }

        }

        // Uses parameters to set object attributes
        // @params: (string) $key, (string) $value
        // @params: (Array) [$key], (Array) [$key => $value]
        public function __set($key, $value)
        {
                $this->$key = $value;
        }

        public function __get($key)
        {
            return $this->$key;
        }

        public function __isset($key)
        {
            return isset($this->$key) ? TRUE : FALSE;
        }

        private function load()
        {
            if ( ! ($this->id || $this->createdby) )
            {
                return false;
            }

            // Fetch contact data for the specified id and uid (createdby),
            //  use the array returned by fields() to alias column names
            $db_res = WebCRMContact::query( $this->id, $this->createdby, self::fields() );

            if ( $this->record = $db_res->fetch_assoc() )
            {
                // Assign DB columns=>values as attributes of the object
                foreach( $this->record as $col => $value )
                {
                    $col = strtolower($col);
                    $this->$col = $value;
                }
            }
        }

        protected function prepare_fields($table=null)
        {
            $fields = array();

            // Select DB Fields based on table
            // @var   $attr_col = Array( $attribute => $column )
            // @desc  Object attribute name is mapped to its destination
            //         column, prefixed with a character to indicate field type
            // @use   array_key($attr_col)
            $attr_col = self::fields($table);

            // Create list of modified fields and create an assoc array
            //  that maps them to their new values
            foreach($attr_col as $attribute => $column)
            {
                list($type, $columnname) = explode('_', $column, 2);

                // Compare Value in Object to its origin
                // @var  $this->record[$attribute]
                // @desc An array of the DB_Result_Obj values
                //       used to initialize the object

                //if ( array_key_exists($columnname, $this->record ) )
                if ( array_key_exists($attribute, $this->record) )
                {
                    if ( $this->$attribute != $this->record[$attribute] )
                    {
                        // Sanitize
                        $this->$attribute = db::escape( trim($this->$attribute) );

                        // Wrap strings in quotes
                        $fields[$columnname] = $type == 's' ? "'".$this->$attribute."'" : $this->$attribute;
                    }
                }
            }

            return $fields;
        }

        public static function fields( $table = 'crm_contacts_view', $field = null )
        {
            // Alias => ColumnName

            $tables = array(

                'crm_contacts' => array(

                    'id'     =>  'i_ID',
                    'firstname' => 's_FirstName',
                    'lastname' => 's_LastName',
                    'email' => 's_Email',
                    'createdby' => 'i_CreatedBy',
                    'createdtime' => 'i_CreatedTime',
                ),

                'crm_contact_profiles' => array(

                    'id'            => 'i_ContactID',
                    'createdby'     => 'i_CreatedBy',
                    'company'     => 'i_CompanyID',
                    'profession'    => 's_Profession',
                    'phone'         => 's_Phone',
                    'mobile'        => 's_Mobile',
                    'fax'           => 'i_Fax',
                    'url'           => 's_URL',
                    'country'       => 'i_Country',
                    'state'         => 'i_State',
                    'zip'           => 'i_Zip',
                    'city'          => 's_City',
                    'address'       => 's_Address',
                ),

                'crm_contacts_view' => array(

                    'id'         =>  'ContactID',
                    'createdby'  =>  'CreatedBy',
                    'firstname'  =>  'FirstName',
                    'lastname'   =>  'LastName',
                    'email'      =>  'Email',
                    'company'    =>  'Company',
                    'profession' =>  'Profession',
                    'phone'      =>  'Phone',
                    'mobile'     =>  'Mobile',
                    'fax'        =>  'Fax',
                    'url'        =>  'URL',
                    'address'    =>  'Address',
                    'address2'   =>  'Address2',
                    'city'       =>  'City',
                    'state'      =>  'State',
                    'zip'        =>  'Zip',
                    'country'    =>  'Country'
                )

            );

            if ( isset($tables[$table][$field]) )
                return $tables[$table][$field];
            else
                return isset($tables[$table]) ? $tables[$table] : FALSE;
        }

        public function save()
        {
            if( ! $result = isset($this->id) ? $this->update() : $this->create() )
            {
                ErrorHandler :: msgNotice('Save Unsuccessful!');
            }

            return $result;
        }

        public function build_query( $action, $table, $where=null )
        {
            switch($action)
            {
                case 'update':

                    // Fetch array that maps ColumnName => Value
                    $fields = $this->prepare_fields($table);

                    // Return if no changes were found
                    if ( ! count($fields) )
                        return false;

                    // Format fields as ColumnName => ColumnName=Value
                    foreach($fields as $column => $value)
                    {
                        $fields[$column] = $column . '=' . $value;
                    }

                    $query_pieces = array(
                        'table'  => db :: prefix($table),
                        'fields' => implode(', ', $fields),
                        'where'  => implode(' AND ', $where)
                    );

                    $query = <<<SQL
UPDATE  {$query_pieces['table']}
SET     {$query_pieces['fields']}
WHERE   {$query_pieces['where']}
SQL;
                    break;

                case 'insert':

                    // Fetch array that maps ColumnName => Value
                    $fields = $this->prepare_fields($table);

                    // Return if no changes were found
                    if ( ! count($fields) )
                        return false;

                    $query_pieces = array(
                        'table'  => db :: prefix($table),
                        'fields' => implode(', ', array_keys($fields)),
                        'values' => implode(', ', $fields),
                    );

                    $query = <<<SQL
INSERT INTO {$query_pieces['table']} (
    {$query_pieces['fields']}
) VALUES ( {$query_pieces['values']} )
SQL;
                    break;
                case 'delete':

                    $query_pieces = array(
                        'table' => db :: prefix($table),
                        'where' => implode(' AND ', $where)
                    );

                    $query = <<<SQL
DELETE FROM {$query_pieces['table']}
WHERE {$query_pieces['where']}
SQL;
                    break;

                default:

                    ErrorHandler :: msgError("Unrecognized statement type ($action) in query");

                    return false;
            }

            return $query;

        }

        private function create()
        {
            // Set createdtime property to current Unix time
            $this->createdtime = time();

            $this->createdby = Auth :: get('UserID');

            if ( $query_save_contacts = $this->build_query('insert', 'crm_contacts') )
            {
                db :: query( $query_save_contacts );

                $this->id = db :: insert_id();
            }
            else
            {
                ErrorHandler :: msgError('Unable to create new Contact! Aborting.');
                return false;
            }

            if ( $query_save_contact_profiles = $this->build_query('insert', 'crm_contact_profiles') )
            {
                db :: query( $query_save_contact_profiles);
            }
            else
            {
                ErrorHandler :: msgError('Unknown Error: Unable to create Contact profile!');
                return false;
            }

            return ( new CRMContact($this->id) );

        }

        public function delete()
        {
            if ( ! isset($this->id) || ($this->id == $this->createdby) )
                return false;

            $where = array("ContactID={$this->id}", "CreatedBy={$this->createdby}");

            if ( $query_delete_contact = $this->build_query('delete', 'crm_contact_profiles', $where) )
            {
                return db :: query( $query_delete_contact );
            }

            return false;
        }

        private function update()
        {
            $where = array("ID={$this->id}");

            if ($query_save_contacts = $this->build_query('update', 'crm_contacts', $where) )
            {
                db :: query( $query_save_contacts );
            }

            $where = array("ContactID={$this->id}", "CreatedBy={$this->createdby}");

            if ( $query_save_contact_profiles = $this->build_query('update', 'crm_contact_profiles', $where) )
            {
                db :: query( $query_save_contact_profiles );
            }

            if ( $query_save_contacts || $query_save_contact_profiles )
            {
                return ( new CRMContact($this->id) );
            }

            return false;
        }

    }
