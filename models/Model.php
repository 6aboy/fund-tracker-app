    <?php

    class Model{
        protected static $conn;
        protected static $table;
        public static function setConnection($conn){
            self::$conn = $conn;
        }

        protected static function all(){
            try{
                $sql = "SELECT * from " . static::$table;
                $stmt = self::$conn->query($sql);
                $rows =  $stmt->fetchAll(); 
                
                return count($rows) > 0 ? $rows : null; 
            }
                
            catch(PDOException $e){
                die("Query failed: " . $e->getMessage());
            }
        }

        protected static function find($id){
            try{
                $sql = "SELECT * from " . static::$table . " WHERE id = :id";
                $stmt = self::$conn->prepare($sql);
                $stmt->bindParam(':id', $id); 
                $stmt->execute(); 
                $row =  $stmt->fetch(); 
                
                if(empty($row)){
                    return null; 
                }
                else{
                    return count($row) > 0 ? $row : null;
                }
            }
                
            catch(PDOException $e){
                die("Query failed: " . $e->getMessage());
            }
        }

        protected static function create(array $data){
            try{
                $columns = implode(", ", array_keys($data));
                $values = implode(", ", array_map(fn($key) => ":$key", array_keys($data)));
                $sql = "INSERT INTO " . static::$table
                    . " ($columns) VALUES ($values)";
    
                $stmt = self::$conn->prepare($sql);
    
                foreach($data as $key => $value){ 
                    $stmt->bindValue(":$key", $value);     
                }
    
                $stmt->execute(); 

                $id = self::$conn->lastInsertId(); 
                return self::find($id); 
            }
            catch(PDOException $e){
                die("Query failed: " . $e->getMessage());
            }
        }

        protected static function updateById($id, array $data){
            try{
                $set = implode(", ", array_map(fn($key) => "$key = :$key", array_keys($data)));
    
                $sql = "UPDATE " . static::$table
                    . " SET $set WHERE id = :id";
                
                $stmt = self::$conn->prepare($sql);
    
                foreach($data as $key => $value){ 
                    $stmt->bindValue(":$key", $value); 
                }
                $stmt->bindValue(':id', $id); 
                $stmt->execute();
    
                return self::find($id);
            }
            catch(PDOException $e){
                die("Query failed: " . $e->getMessage());
            }
        }

        protected static function deleteById($id){
            try{
                $sql = "DELETE FROM " . static::$table
                . " WHERE id = :id";
    
                $stmt = self::$conn->prepare($sql);
                $stmt->bindValue(':id', $id); 
                $stmt->execute(); 
            }
            catch(PDOException $e){
                die("Query failed: " . $e->getMessage());
            }
        }

        protected static function where($column, $operator, $value){
            try{
                //  SELECT * FROM table(e.g. users) WHERE column(e.g. name) operator(e.g. =, >, <, >=, <=, !=) value(e.g. 'John Doe')
                $sql = "SELECT * FROM " . static::$table . " WHERE $column $operator :value"; 
    
                $stmt = self::$conn->prepare($sql);
    
                $stmt->bindValue(':value', $value);
    
                $stmt->execute(); 
    
                $rows =  $stmt->fetchAll(); 
    
                return count($rows) > 0 ? $rows : null;
            }
            catch(PDOException $e){
                die("Query failes: " . $e->getMessage());
            }
        }

        protected function belongsToMany($relatedClass, $pivotTable, $foreignKey, $relatedKey){
            try{
                $relatedTable = $relatedClass::$table;
                $sql = "SELECT rt.* FROM $relatedTable rt INNER JOIN $pivotTable pt ON rt.id = pt.$relatedKey WHERE pt.$foreignKey = :id";
                $stmt = self::$conn->prepare($sql);
                $stmt->bindValue(':id', $this->id);
                $stmt->execute();
                $rows = $stmt->fetchAll();

                return $rows ? array_map(fn($row) => new $relatedClass($row), $rows) : null;
            }
            catch(PDOException $e){
                die("Query failed: " . $e->getMessage());
            }
        }
    }