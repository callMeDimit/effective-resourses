<?php
namespace woo\mapper;


require_once( "woo/base/Exceptions.php" );
require_once( "woo/mapper/Mapper.php" );
require_once( "woo/mapper/VenueMapper.php" );
require_once( "woo/mapper/Collections.php" );
require_once( "woo/domain.php" );

class SpaceMapper extends Mapper 
                             implements \woo\domain\SpaceFinder {

    function __construct() {
        parent::__construct();
        $this->selectAllStmt = self::$PDO->prepare(
                            "SELECT * FROM space");
        $this->selectStmt = self::$PDO->prepare(
                            "SELECT * FROM space WHERE id=?");
        $this->updateStmt = self::$PDO->prepare(
                            "UPDATE space SET name=?, id=? WHERE id=?");
        $this->insertStmt = self::$PDO->prepare(
                            "INSERT into space ( name, venue ) 
                             values( ?, ?)");
        $this->findByVenueStmt = self::$PDO->prepare(
                            "SELECT * FROM space where venue=?");
    } 
    
    protected function targetClass() {
        return "\woo\domain\Space";
    }

    protected function doInsert( \woo\domain\DomainObject $object ) {
        $venue = $object->getVenue();
        if ( ! $venue ) { 
            throw new \woo\base\AppException( "cannot save without venue" );
        }
        $values = array( $object->getname(), $venue->getId() ); 
        $this->insertStmt->execute( $values );
        $id = self::$PDO->lastInsertId();
        $object->setId( $id );
    }
    
    function update( \woo\domain\DomainObject $object ) {
        $values = array( $object->getname(), $object->getid(), $object->getId() ); 
        $this->updateStmt->execute( $values );
    }

    function selectStmt() {
        return $this->selectStmt;
    }

    function selectAllStmt() {
        return $this->selectAllStmt;
    }

    # custom
    function findByVenue( $vid ) {
        $this->findByVenueStmt->execute( array( $vid ) );
        return new SpaceCollection( $this->findByVenueStmt->fetchAll(), $this->getFactory()->getDomainObjectFactory() );
    }
    # end_custom
}
