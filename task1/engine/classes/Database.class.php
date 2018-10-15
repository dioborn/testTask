<?php

/**
 * @abstract Advanced database functions whitch may be used in other classes
 * Use class for specified database to access databases
 */
abstract class advancedDatabase {

    const DATE_SQL = "Y-m-d";
    const DATETIME_SQL = "Y-m-d H:i:s";

    /**
     * Converts special column names
     * @param Array $value Array of values
     * @param Array $field Array of column names
     */
    protected function valueReinterpret(&$value, &$field) {

        # Check if value is correct
        if (!is_numeric($value) && !is_string($value) && !is_null($value) && !is_bool($value))
            throw new ErrorException("Wrong value variable: " . var_export($value));


        # Adds dashes
        if (strstr($field, 'NO_QUOTES '))
            $field = str_replace('NO_QUOTES ', '', $field);
        elseif (!is_null($value) && !is_numeric($value))
            $value = "'" . addslashes($value) . "'";


        # If we need to replace password
        if (strstr($field, 'PASSWORD_VALUE ')) {
            $field = str_replace('PASSWORD_VALUE ', '', $field);
            $value = "PASSWORD($value)";
        } elseif (strstr($field, 'INET_ATON')) {
            $field = str_replace('INET_ATON ', '', $field);
            $value = "INET_ATON($value)";
        }

        # Add dashes
        $field = $this->addDashes($field);
    }

    /**
     * This generates string for WHERE expression based on parameters
     * @param String $location  Table name
     * @param Array $conditions Special conditions array(See full documentation)
     * @param Array $fields     Names of columns for values in $conditions
     * @param Array $parameters Array with special keys for additional parmeters
     * @return string           String format like "WHERE id='1' AND date > '10.10.2011'"
     *
     */
    public function makeWhereExpression($location, $conditions = false, $fields = false, &$parameters = false) {

        # If no conditions
        if ($conditions === false || (is_array($conditions) && sizeof($conditions) == 0))
            return "";# Where exp is emty
        # Check fields for conditions
        if ($fields !== false) {

            # Check format
            if (!is_string($fields) && !is_array($fields))
                throw new ErrorException("Database query error: wrong fields format");

            # Check size
            if (is_array($fields) && sizeof($fields) !== sizeof($conditions))
                throw new ErrorException("Database query error: number of fields doesn't equal to values");
        } elseif (is_array($conditions) && empty($parameters["compares"])) {

            $idOnly = true;

            # Check if all values and keys numeric
            foreach ($conditions as $key => $value)
                if (!is_numeric($key) || !is_numeric($value))
                    $idOnly = false;

            # If this is list of ids
            if ($idOnly)
                $conditions = array(array("id", $conditions, "IN"));
        }
        elseif (!is_array($conditions))
            $parameters["return"] = "single";


        # Converting conditions to array
        if (!is_array($conditions) || is_string($fields))
            $conditions = array($conditions);


        # Check compares for conditions
        if (isset($parameters['compares'])) {

            # Its size should be equal to conditions size
            if (is_array($parameters['compares']) && sizeof($parameters['compares']) != sizeof($conditions))
                throw new ErrorException("Database query error: number of compares doesn't equal to values");
        }


        # Check AND's and OR's for conditions
        if (isset($parameters['logics'])) {

            # Its size should be equal to conditions size minus one
            if (is_array($parameters['logics']) && sizeof($parameters['logics']) != (sizeof($conditions) - 1))
                throw new ErrorException("Database query error: number of logics doesn't equal to values minus one");
        }


        # Variables initialization
        $whereExpression = "WHERE ";
        $i = 0;
        $groupIndex = 0;  # Group index
        $fieldsNumber = sizeof($conditions);
        $lb = "";  # Left bracket
        $rb = "";  # Right bracket
        $groupElement = 0;  # Index of element in group
        $valueLogic = false; # Array value flag
        $keys = array_keys($conditions);


        # Making WHERE expression
        while ($i < sizeof($conditions)) {


            # Set compare
            if (isset($parameters['compares'])) {

                # For array we will get next element
                if (is_array($parameters['compares']))
                    $currentCompare = $parameters['compares'][$i];
                else
                    $currentCompare = $parameters['compares'];# For not an array itself
            }
            else
                $currentCompare = "=";


            # Set logick
            if (isset($parameters['logics'])) {

                # If logick if condition already used
                if ($valueLogic)
                    throw new ErrorException("Can't use both logics array and logics in conditions");

                # For array we will get next element
                if (is_array($parameters['logics']) && isset($parameters['logics'][$i]))
                    $currentLogick = $parameters['logics'][$i];
                else
                    $currentLogick = $parameters['logics'];# For not an array itself
            }
            else
                $currentLogick = "AND";


            # Gathering fields and values
            if ($fields !== false) {

                # Set them by $i
                if (is_string($fields))
                    $field = $fields;# For one set
                else
                    $field = $fields[$i];# For Array
                $value = $conditions[$i];
            }
            # If value setted as array
            elseif (isset($conditions[$i]) && is_array($conditions[$i])) {

                # If wrong format
                if (sizeof($conditions[$i]) < 2 || sizeof($conditions[$i]) > 4)
                    throw new ErrorException("Wrong value array format: serialized: " . serialize($conditions[$i]));

                # Fill vars
                $valueLogic = true;       # Marks that we use logick in conditions
                if (sizeof($conditions[$i]) > 3)
                    $currentLogick = $conditions[$i][3];# Logick
                if (sizeof($conditions[$i]) > 2)
                    $currentCompare = $conditions[$i][2];# Compare
                $value = $conditions[$i][1]; # Value
                $field = $conditions[$i][0]; # Field
            }
            else {
                $field = $keys[$i];
                $value = $conditions[$field]; # If no fields set we'll use keys
            }


            # Check if key usable
            if (is_numeric($field))
                $field = $location . ".id";


            # If we have groups its it initialization
            if ($groupElement == 0) {
                if (isset($parameters['groups'][$groupIndex]))
                    $groupElement = 1;
                if (isset($parameters['groups']) && $parameters['groups'] != false && !is_numeric($parameters['groups']))
                    $groupElement = 1;
            }


            # Adds bracket for first element in group
            if ($groupElement == 1)
                $lb = "(";
            else
                $lb = "";


            # Adds bracket for last element in group
            $rb = "";
            if ($groupElement >= 1) {
                # Check end of group conditions
                if ((is_array($parameters['groups']) && $parameters['groups'][$groupIndex] == $groupElement) || # If groups is array
                        (!is_array($parameters['groups']) && $parameters['groups'] == $groupElement)) {    # If it's not
                    $rb = ")";
                    $groupElement = 0;
                    $groupIndex++;
                }
            }


            # Incriment counter of elements added in group
            if ($groupElement >= 1)
                $groupElement++;


            /* Of compare is IN */
            if ($currentCompare == "IN" || $currentCompare == "NOT IN") {
                if (is_array($value)) {

                    $a = "";

                    # Empty array
                    if (empty($value))
                        throw new ErrorException("Empty array set as IN value");

                    if (sizeof($value) == 1) {
                        $currentCompare = $currentCompare == "IN" ? "=" : "!=";
                        self::valueReinterpret($value[0], $a);
                        $value = $value[0];
                    } else {
                        foreach ($value as &$item)
                            self::valueReinterpret($item, $a);
                        unset($item);
                        $value = implode(", ", $value);
                        $value = '(' . $value . ')';
                    }
                }
                else
                    $value = '(' . $value . ')';
            }
            elseif ($field !== "USE_AS_IS")
                self::valueReinterpret($value, $field);


            # Null compare converting
            if (is_null($value) && $currentCompare == "=")
                $currentCompare = "IS";
            if (is_null($value) && $currentCompare == "!=")
                $currentCompare = "IS NOT";


            # Add logic of it setted in value
            if ($valueLogic && $i != 0)
                $whereExpression .= " " . $currentLogick . " ";


            # Make expression
            if ($field !== "USE_AS_IS")
                $whereExpression .= $lb . $field . " " . $currentCompare . " " . (is_null($value) ? 'NULL' : $value) . $rb;
            else
                $whereExpression .= $lb . $value . $rb;


            # Adds AND's and OR's
            $i++;
            if (!$valueLogic && $i != $fieldsNumber)
                $whereExpression .= " " . $currentLogick . " ";
        }

        # Return generated value
        return $whereExpression;
    }

    /**
     * Adds special dashes to column names
     * @param String $field Column name
     */
    public function addDashes($field) {

        # Add back upper coma
        $field = trim($field);
        if (strstr($field, " AS ") || strstr($field, " as "))
            return $field;# If not needed
        $field = str_replace('`', '', $field);  # Replace old if set
        $field = str_replace('.', '`.`', $field); # Dot separate
        $field = "`$field`";      # Separate all
        $field = str_replace('`*`', '*', $field); # Star match
        return $field;
    }

    /**
     * Implodes name list and add dashes
     * @param Array $names Column names
     */
    public function implodeNames($names) {

        $result = "";

        # Check
        if (!is_array($names))
            throw new ErrorException("Not an array");

        # Go thru
        foreach ($names as $name) {

            if ($result !== "")
                $result .= ", ";
            $result .= $this->addDashes($name);
        }
        return $result;
    }

}

/**
 * This class will provide main abstraction level to
 * work with databases
 */
class Database extends advancedDatabase {

	public $Errors = array();
    private
    		$debug = true,
            $trace = false,
            $queryCount, # Number of queries
            $databaseType, # Database type
            $databaseHost, # MySQL host address
            $databaseName, # Database name
            $databaseUser, # User name
            $databasePassword, # Password
            $databaseLink, # Pointer to database connection
            $joinTypes = array("left", "right", "inner"),
            $returnTypes = array("all", "single", "cursor", "query", "updated", "id", "none"),
            $traceTypes = array(true, "time", "safe"),
            $parametersTypes = array("group" => "multi",
                "having" => "string",
                "order" => "multi",
                "join" => "multi",
                "records" => "multi",
                "offset" => "int",
                "limit" => "int"
    );

    /**
     * Creates new database connector instance, works thru PDO
     * @param String $address	Database url or ip
     * @param String $database	Database name
     * @param String $user		User name to access
     * @param String $password	Password to access
     * @param String $type		Type of database, all types which are imported in to your PDO class
     */
    public function __construct($conf) {

        $this->databaseHost = $conf['DB_ADDR'];
        $this->databaseName = $conf['DB_NAME'];
        $this->databaseUser = $conf['DB_USER'];
        $this->databasePassword = $conf['DB_PASS'];
        $this->databaseCharset = $conf['DB_CHARSET'];
        $this->databaseType = "mysql";
        $this->queryCount = 0;

        return $this->connect();
    }

    /**
     * Closes connection
     */
    public function __destruct() {
        $this->databaseLink = NULL;
    }

    /**
     * Return database connection object
     */
    private function connect() {

        //try {
        # Checks connection parameters
        if (!isset($this->databaseHost) || !isset($this->databaseName) || !isset($this->databaseUser) || !isset($this->databasePassword))
            throw new ErrorException("Connection parameters doesn't initialized");

        # Checks if link exists
        if (isset($this->databaseLink))
            return $this->databaseLink;



        $link = @new PDO($this->databaseType . ":dbname=" .
                        $this->databaseName . ";host=" .
                        $this->databaseHost,
                        $this->databaseUser,
                        $this->databasePassword,
                        array(PDO::ATTR_PERSISTENT => true));

        $data = $link->query('SET NAMES ' . $this->databaseCharset);
        $data->closeCursor();
        /* } catch (PDOException $e) {
          $App = Application::getInstance();
          $App->eh->SetErrorMessage('database', $e->getMessage());
          return false;
          } catch (ErrorException $e) {
          $App = Application::getInstance();
          $App->eh->SetErrorMessage('database', $e->getMessage());
          return false;
          } */

        # If all ok
        return $this->databaseLink = $link;  # Save link to DB in global variable
    }

    /**
     * Performs query
     * @param String $query  Query string to be performed
     * @param String $return Return data type
     * @param String $trace	 Tracing query type
     * @throws ErrorException
     */
    public function query($query, $return = false, $trace = false) {
    	if($this->debug)
    		$this->Errors[] = $query;
        $trace = $trace || $this->trace;

        try {
            # Check return type
            if ($return && !in_array($return, $this->returnTypes))
                throw new ErrorException("Wrong return type: " . $return);


            # Check trace type
            if ($trace && !in_array($trace, $this->traceTypes))
                throw new ErrorException("Wrong trace type: " . $trace);


            # Connect if needed
            $link = $this->connect();




            # Tracing output
            if ($trace)
                echo "Query was: $query";
            if ($trace === "safe")
                return false;
            if ($return === "query")
                return $query;


            # Perform request
            $this->statement = $link->prepare($query);


            # Is tatement not prepared
            if ($this->statement === false) {
                $error = $this->databaseLink->errorInfo();
                throw new ErrorException("Database query error: " . $error[2] . "\nQuery was: " . $query);
            }


            # Executes query
            $error = $this->statement->execute();


            # If error occupired
            if ($error === false) {
                $error = $this->statement->errorInfo();
                throw new ErrorException("Database query error: " . $error[2] . "\nQuery was: " . $query);
            }


            # Query count incrimentation
            $this->queryCount++;


            # Cursor return
            if ($return === "cursor")
                return $this->statement;
            if ($return === "none")
                return true;


            # Get result data
            if ($return === "single")
                $data = $this->statement->fetch(PDO::FETCH_ASSOC);
            else
                $data = $this->statement->fetchAll(PDO::FETCH_ASSOC);


            # Get updated
            if ($return == "updated")
                return $this->statement->rowCount();


            # Clear cursor
            $this->statement->closeCursor();
            //$statement = null;
            # Additional returns
            if ($return === "id")
                return $this->databaseLink->lastInsertId();
            if (!$data)
                return false;

            return $data;
        } catch (PDOException $e) {
            $App = Application::getInstance();
            if($this->debug) $this->Errors[]=$e->getMessage();
            //$App->eh->SetErrorMessage('database', $e->getMessage());
            return false;
        } catch (ErrorException $e) {
            $App = Application::getInstance();
            if($this->debug) $this->Errors[]=$e->getMessage();
            //$App->eh->SetErrorMessage('database', $e->getMessage());
            return false;
        }
    }

    /**
     * Gets query string parts from parameters
     * @param String $name Part name
     * @param Mixed $value Value for expression generation
     * @throws ErrorException
     */
    private function getQueryPart($name, $value) {


        # Check type
        if (is_array($value) && $this->parametersTypes[$name] !== "multi")
            throw new ErrorException("This parameter can't be array: " . $name);


        # Prepare expression
        switch ($name) {
            case "group": $expression = "GROUP BY ";
                break;
            case "order": $expression = "ORDER BY ";
                break;
            case "limit": $expression = "LIMIT ";
                break;
            case "offset": $expression = "OFFSET ";
                break;
            case "having": $expression = "HAVING ";
                break;
            default: $expression = "";
        }


        # Empty parameters
        if ($value === false)
            return "";


        # If array
        if (is_array($value)) {
            switch ($name) {
                case "join": {


                        # Retranslate single join to array structure
                        if (!isset($value[0]))
                            $value = array($value);


                        # Multiple joins
                        foreach ($value as $join) {

                            # If join is a string we just put it
                            if (is_string($join)) {
                                $expression .= $join;
                                continue;
                            }


                            # Check if table set
                            if (empty($join["table"]))
                                throw new ErrorException("Table not set in join data");


                            # Adds space for second join
                            if (!empty($expression))
                                $expression .= " ";


                            # Adds join type
                            if (!isset($join["type"]) || !in_array($join["type"], $this->joinTypes))
                                $expression .= "LEFT JOIN " . $join["table"] . " ";
                            else
                                $expression .= $join["type"] . " JOIN " . $join["table"];


                            # If we have ON clause in join
                            if (isset($join["on"])) {


                                # Simple string on expression
                                if (is_string($join["on"])) {
                                    $expression .= " ON " . $join["on"];
                                    continue;
                                }


                                # Check type
                                if (!is_array($join["on"]))
                                    throw new ErrorException("Wrong join on value: " . $join["on"]);


                                # Go  thru params
                                foreach ($join["on"] as $key => $compare) {

                                    # Adds on of not added yet
                                    if ($key === 0)
                                        $expression .= " ON ";


                                    # If this is string with compare
                                    if (is_string($compare)) {
                                        if ($key !== 0)
                                            $expression .= " AND ";
                                        $expression .= $compare;
                                        continue;
                                    }


                                    # Check value
                                    if (!is_array($compare) || sizeof($compare) < 2)
                                        throw new ErrorException("Wrong ON value for JOIN in array");


                                    # Add logick for this compare
                                    if ($key && sizeof($compare) < 4)
                                        $expression .= " AND ";
                                    elseif ($key)
                                        $expression .= " " . $compare[3] . " ";

                                    # Make statement
                                    $this->valueReinterpret($compare[1], $compare[0]);
                                    if (!isset($compare[2]))
                                        $compare[2] = "=";
                                    $expression .= $compare[0] . " " . $compare[2] . " " . $compare[1];
                                }

                                # Go to next join
                                continue;
                            }


                            # If we haven't USING instead of ON
                            if (empty($join["using"]))
                                continue;


                            # If using simple string we just put it
                            if (is_string($join["using"])) {
                                $expression .= "USING({$join["using"]})";
                                continue;
                            }


                            # If array we will make string with , as separator
                            if (is_array($join["using"]))
                                $expression .= "USING(" . $this->implodeNames($join["using"]) . ")";
                        }
                        break;
                    }
                case "order": {
                        foreach ($value as $field => $order) {


                            # Add comma
                            if ($expression !== "ORDER BY ")
                                $expression .= ", ";


                            # If simple string
                            if (is_numeric($field)) {
                                if (!strstr($order, " ASC") && !strstr($order, " DESC"))
                                    $order = $order . " ASC";
                                $expression .= " " . $order;
                                continue;
                            }


                            # Add dashes
                            if (!strstr($field, "("))
                                $this->addDashes($field);


                            # Make order
                            $expression .= " " . $field . " " . strtoupper($order);
                        }
                        break;
                    }
                default: $expression .= $this->implodeNames($value);
                    break;
            }
            return $expression;
        }

        # If $value not array

        switch ($name) {
            case "offset": if ($value < 1)
                    return ""; $expression .= $value;
                break;
            case "order" : if (!strstr($value, " ASC") && !strstr($value, " DESC"))
                    $value = $value . " DESC";
            default: $expression .= $value;
        }

        return $expression;
    }

    /**
     * Gets rows from database which was initializated by SQL::initialize.
     * @param String $location  Name of table to perform actions
     * @param Array $conditions Array of conditions for rows to update(See full documentation).
     * @param Array $fields     Array of column names which were used in <b>conditions</b>
     * @param Array $parameters Array of special parameters for conditions(See full documentation).
     * @param Bool  $trace      Boolean parameter, if TRUE then SQL request will be printed.
     * @return various FALSE on no record or mysql_result identifier
     */
    public function get($location, $conditions = false, $fields = false, $parameters = false, $trace = false) {

        try {

            # Converting
            if (is_array($fields) && !isset($fields[0])) {
                $parameters = $fields;
                $fields = false;
            }
            if (is_string($fields) && is_array($conditions) && !isset($conditions[0])) {
                $parameters = $fields;
                $fields = false;
            }
            if (is_string($parameters))
                $parameters = array("return" => $parameters);


            # Parameters generation
            $whereExpression = parent::makeWhereExpression($location, $conditions, $fields, $parameters);
            $groupExpression = "";
            $havingExpression = "";
            $orderExpression = $this->oldOrderBy($parameters); //"";
            $limitExpression = "";
            $offsetExpression = "";
            $joinExpression = "";
            $recordsExpression = "*";


            # Get each string parameter
            if (is_array($parameters)) {
                foreach ($parameters as $name => $value) {

                    # Check name
                    if (!in_array($name, array_keys($this->parametersTypes)))
                        continue;

                    ${$name . "Expression"} = $this->getQueryPart($name, $value);
                }
            }


            # Prepare statement
            $requestString = "SELECT $recordsExpression FROM $location" .
                    " $joinExpression" .
                    " $whereExpression" .
                    " $groupExpression" .
                    " $havingExpression" .
                    " $orderExpression" .
                    " $limitExpression" .
                    " $offsetExpression";


            # Set what to return
            $return = false;
            if (isset($parameters['return']))
                $return = $parameters['return'];
        } catch (ErrorException $e) {
            $App = Application::getInstance();
            if($this->debug) $this->Errors[]=$e->getMessage();
//            $App->eh->SetErrorMessage('database', $e->getMessage());
            return false;
        }


        # Performs query
        return $this->query($requestString, $return, $trace, false);
    }

    /* Обратная совместимость со старой версией библиотеки */
    protected function oldOrderBy(&$parameters) {
        # ORDER BY parameter
        $orderByExpression = "";
        if (!isset($parameters['orderBy']))
            return $orderByExpression;

        $orderId = 0;
        if (is_array($parameters['orderBy'])) {

            foreach ($parameters['orderBy'] as $orderBy) {

                if (!empty($parameters['order'])) {
                    if (is_array($parameters['order']) && sizeof($parameters['order']) == sizeof($parameters['orderBy'])) {
                        $order = $parameters['order'][$orderId];
                        $orderId++;
                    }
                    else
                        $order = !empty($parameters['order']) ? strtoupper($parameters['order']) : "DESC";
                } else
                    $order = "DESC";


                # Generation order by

                if ($orderByExpression === "")
                    $orderByExpression = "ORDER BY";
                else
                    $orderByExpression .= ", ";

                $orderByExpression .= " " . $orderBy . " " . $order;
            }
        } elseif (is_string($parameters['orderBy'])) {

            $order = empty($parameters['order']) || strtoupper($parameters['order']) != "ASC" ? "DESC" : strtoupper($parameters['order']);
            # Generation order by
            $orderByExpression = "ORDER BY " . $parameters['orderBy'] . " " . $order;
        }

        unset($parameters['order'], $parameters['orderBy']);

        return $orderByExpression;
    }

    /**
     * Updates record in database which was initialized by SQL::initialize.
     * @param String $location  Name of table to perform actions
     * @param Array $conditions Array of conditions for rows to update(See full documentation).
     * @param Array $records    Array of fields to update.
     * @param Array $fields     Array of column names which were used in <b>conditions</b>
     * @param Array $parameters Array of special parameters for conditions(See full documentation).
     * @param Bool  $trace      Boolean parameter, if TRUE then SQL request will be printed.
     * @return Boolean          TRUE if any rows were updated, and FALSE of no one
     */
    public function update($location, $conditions, $records = array(), $fields = false, $parameters = false, $trace = false) {

        if (!is_array($records))
            return $this->update_old($location, $conditions, $records, $fields);

        try {

            $expression = '';

            $whereExpression = parent::makeWhereExpression($location, $conditions, $fields, $parameters);

            while (list($field, $value) = each($records)) {
                if ($expression != '')
                    $expression .= ', ';

                # Expression generation
                self::valueReinterpret($value, $field);
                $expression .= $field . "=" . $value;
            }

            $requestString = "UPDATE $location SET $expression $whereExpression";
        } catch (ErrorException $e) {
            $App = Application::getInstance();
            if($this->debug) $this->Errors[]=$e->getMessage();
//            $App->eh->SetErrorMessage('database', $e->getMessage());
            return false;
        }

        # Performs query
        return $this->query($requestString, 'updated', $trace);
    }

    /**
     * Adds one row to table of database which was initializated by SQL::initialize.
     * @param String $location  Name of table to perform actions.
     * @param Array $records    Array of fields to insert .
     * @param Bool  $trace      Boolean parameter, if TRUE then SQL request will be printed.
     * @return Boolean return TRUE on success or throws ErrorException on any error.
     */
    public function add($location, $records, $trace = false) {
        try {

            # Initialization
            $fieldsExpression = "";
            $valuesExpression = "";

            while (list($field, $value) = each($records)) {

                # Add " ," if needed
                if ($fieldsExpression != "")
                    $fieldsExpression .= ", ";
                if ($valuesExpression != "")
                    $valuesExpression .= ", ";

                # Reinterpret values
                self::valueReinterpret($value, $field);

                # Add field to list
                $fieldsExpression .= $field;

                # Add value to list
                $valuesExpression .= $value;
            }

            $requestString = "INSERT INTO $location($fieldsExpression) VALUES($valuesExpression)";
        } catch (ErrorException $e) {
            $App = Application::getInstance();
            if($this->debug) $this->Errors[]=$e->getMessage();
//            $App->eh->SetErrorMessage('database', $e->getMessage());
            return false;
        }

        # Performs query
        return $this->query($requestString, 'id', $trace);
    }

    /**
     * Deletes records from database which was initialized by SQL::initialize.
     * @param String $location  Name of table to perform actions
     * @param Array $conditions Array of conditions for rows to update(See full documentation).
     * @param Array $fields     Array of column names which were used in <b>conditions</b>
     * @param Array $parameters Array of special parameters for conditions(See full documentation).
     * @param Bool  $trace      Boolean parameter, if TRUE then SQL request will be printed.
     * @return Boolean          TRUE if any rows were updated, and FALSE of no one
     */
    public function delete($location, $conditions = false, $fields = false, $parameters = false, $trace = false) {

        // Поддержка старой функции
        if (is_string($conditions) && strpos($conditions, '='))
            return $this->delete_old($location, $conditions);

        try {

            # making WHERE expression
            $whereExpression = parent::makeWhereExpression($location, $conditions, $fields, $parameters);

            $requestString = "DELETE FROM $location $whereExpression";
        } catch (ErrorException $e) {
            $App = Application::getInstance();
            if($this->debug) $this->Errors[]=$e->getMessage();
//            $App->eh->SetErrorMessage('database', $e->getMessage());
            return false;
        }

        # Performs query
        return $this->query($requestString, "updated", $trace);
    }

    /**
     * Adds an array of rows to database which was initializated by SQL::initialize.
     * @param String $location  Name of table to perform actions
     * @param Array $records    Array of fields to insert .
     * @param Bool  $trace      Boolean parameter, if TRUE then SQL request will be printed.
     * @return Various          Retuns array if inserted Ids. Throws mysql_result identifier.
     */
    public function addMultiple($location, $records, $recordsWithIds = false, $trace = false) {

        $ids = false;

        # Foreach record we make new request
        foreach ($records as &$record) {

            # Add record
            $ids[] = $this->add($location, $record, $trace);

            # Add id to records
            if ($recordsWithIds)
                $record['id'] = $ids[sizeof($ids) - 1];
        }

        if ($recordsWithIds)
            return $records;
        return $ids;
    }

    /**
     * Returns pointer for current connection
     * @return MySQL connection resource or FALSE if no connection inintializated
     */
    public function getLink() {
        return $this->databaseLink;
    }

    /*     * **************************************************
     *              Поддержка старых функций            *
     * ************************************************** */

    /* Filed's type of query-id by offset */
    public function EscapeString($string) {
        $link = $this->connect();

        return substr($link->quote($string), 1, -1);
    }

    /* Number of fetched rows */
    public function GetRowsNumber() {
        if (empty($this->statement))
            return false;
        return $this->statement->rowCount();
    }

    /* Number of affected rows */
    public function GetAffectedRows() {
        if (empty($this->statement))
            return false;
        return $this->statement->rowCount();
    }

    /* Number of fetched fields */
    public function GetFieldsNumber() {
        if (empty($this->statement))
            return false;
        return $this->statement->columnCount();
    }

    /* Fetch (next) row */
    public function FetchRow() {
        if (empty($this->statement))
            return false;
        return $this->statement->fetch(PDO::FETCH_BOTH);
    }

    /* Fetch row set (assoc & numbers) */
    public function FetchRowSet($fetchStyle = false) {
        if (empty($this->statement))
            return false;

        if (!$fetchStyle)
            $fetchStyle = PDO::FETCH_BOTH;

        return $this->statement->fetchAll($fetchStyle);
    }

    /* Fetch row set (assoc & numbers) */
    public function FetchAllRowSet() {
        return $this->FetchRowSet();
    }

    /* Fetch row set (assoc) */
    public function FetchAssocRowSet() {
        return $this->FetchRowSet(PDO::FETCH_ASSOC);
    }

    /* Fetch row set (number) */
    public function FetchNumberRowSet() {
        return $this->FetchRowSet(PDO::FETCH_NUM);
    }

    /* Seek rows */
    public function SeekRows($rownum) {
        for ($i = 0; $i < $rownum; $i++) {
            $this->FetchRow();
        }

        return true;
    }

    public function FreeResult() {
        return true;
    }

    /* Return MySQL error */
    public function GetError() {
        $link = $this->connect();
        $result = array();

        $result['code'] = $link->errorCode();
        $result['message'] = $link->errorInfo();

        return $result;
    }

    public function Select($tables, $fields = "*", $where = "", $group = "", $having = "", $order = "", $limit = "") {

        $query = 'SELECT ' . $fields . ' FROM ' . $tables;
        if ((strlen($where)) != "")
            $query .= " WHERE (" . $where . ")";
        if ((strlen($group)) != "")
            $query .= " GROUP BY " . $group;
        if ((strlen($having)) != "")
            $query .= " HAVING (" . $having . ")";
        if ((strlen($order)) != "")
            $query .= " ORDER BY " . $order;
        if ((strlen($limit)) != 0)
            $query .= " LIMIT " . $limit;

        $this->query($query, 'none');

        return $this->FetchRowSet();
    }

    /* SQL Insert */
    public function Insert($table, $columns, $values) {
        $query = 'INSERT INTO ' . $table . ' (' . $columns . ') VALUES (' . $values . ')';

        return $this->query($query, 'updated');
    }

    /* SQL Insert */
    public function InsertIgnore($table, $columns, $values) {
        $query = 'INSERT IGNORE INTO ' . $table . ' (' . $columns . ') VALUES (' . $values . ')';

        return $this->query($query, 'updated');
    }

    /* SQL Insert */
    public function InsertDelayed($table, $columns, $values) {
        $query = 'INSERT DELAYED INTO ' . $table . ' (' . $columns . ') VALUES (' . $values . ')';

        return $this->query($query, 'updated');
    }

    /* SQL Insert */
    public function InsertArray($table, &$columnsValues) {
        $columns = "";
        $values = "";
        foreach ($columnsValues as $col => $val) {
            $columns .= ", `" . $col . "`";
            $values .= ", '" . $this->EscapeString($val) . "'";
        }
        $query = 'INSERT INTO ' . $table . ' (' . substr($columns, 2) . ') VALUES (' . substr($values, 2) . ')';

        return $this->query($query, 'updated');
    }

    /* SQL Insert */
    public function InsertArrayDelayed($table, &$columnsValues) {
        $columns = "";
        $values = "";
        foreach ($columnsValues as $col => $val) {
            $columns .= ", `" . $col . "`";
            $values .= ", '" . $this->EscapeString($val) . "'";
        }
        $query = 'INSERT DELAYED INTO ' . $table . ' (' . substr($columns, 2) . ') VALUES (' . substr($values, 2) . ')';

        return $this->query($query, 'updated');
    }

    /* SQL Insert */
    public function InsertArrayIgnore($table, &$columnsValues) {
        $columns = "";
        $values = "";
        foreach ($columnsValues as $col => $val) {
            $columns .= ", `" . $col . "`";
            $values .= ", '" . $this->EscapeString($val) . "'";
        }
        $query = 'INSERT IGNORE INTO ' . $table . ' (' . substr($columns, 2) . ') VALUES (' . substr($values, 2) . ')';

        return $this->query($query, 'updated');
    }

    /* SQL Delete */
    public function delete_old($table, $where) {
        $query = 'DELETE FROM ' . $table . ' WHERE ' . $where;

        return $this->query($query, 'updated');
    }

    /* SQL Update */
    public function update_old($table, $field, $value, $where = "") {
        $query = 'UPDATE ' . addslashes($table) . ' SET ' . $field . '=' . $value;

        if ($where != "")
            $query .= " WHERE ($where)";

        return $this->query($query, 'updated');
    }

    /* SQL Update more then one fields ($fields and $values are arrays) */
    public function MultiUpdate($table, $fields_values, $where = "") {
        $update = "";
        foreach ($fields_values as $name => $value) {
            $update .= ", " . $name . "='" . $this->EscapeString($value) . "'";
        }
        $update = substr($update, 2);
        $query = 'UPDATE ' . $table . ' SET ' . $update;

        if ($where != "")
            $query .= " WHERE ($where)";

        return $this->query($query, 'updated');
    }

    /* SQL Update more then one fields ($fields_values is array) */
    public function MultiUpdate2($table, $fields_values, $where = "") {
        return $this->MultiUpdate($table, $fields_values, $where);
    }

    /* SQL Select Assoc */
    public function SelectAssoc($tables, $fields = "*", $where = "", $group = "", $having = "", $order = "", $limit = "") {
        $query = 'SELECT ' . $fields . ' FROM ' . $tables;
        if ((strlen($where)) != "")
            $query .= " WHERE (" . $where . ")";
        if ((strlen($group)) != "")
            $query .= " GROUP BY " . $group;
        if ((strlen($having)) != "")
            $query .= " HAVING (" . $having . ")";
        if ((strlen($order)) != "")
            $query .= " ORDER BY " . $order;
        if ((strlen($limit)) != 0)
            $query .= " LIMIT " . $limit;

        $this->query($query, 'none');
        return $this->FetchAssocRowSet();
    }

    /* SQL Select Number */
    public function SelectNumber($tables, $fields = "*", $where = "", $group = "", $having = "", $order = "", $limit = "") {
        $query = 'SELECT ' . $fields . ' FROM ' . $tables;
        if ((strlen($where)) != "")
            $query .= " WHERE (" . $where . ")";
        if ((strlen($group)) != "")
            $query .= " GROUP BY " . $group;
        if ((strlen($having)) != "")
            $query .= " HAVING (" . $having . ")";
        if ((strlen($order)) != "")
            $query .= " ORDER BY " . $order;
        if ((strlen($limit)) != 0)
            $query .= " LIMIT " . $limit;

        $this->query($query, 'none');
        return $this->FetchNumberRowSet();
    }

    /* Return id of inserted row */
    public function InsertAndGetID($table, $columns, $values) {
        $this->Insert($table, $columns, $values);

        return $this->databaseLink->lastInsertId();
    }

}

