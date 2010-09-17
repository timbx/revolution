<?php
/**
 * Copyright 2010 by MODx, LLC.
 *
 * This file is part of xPDO.
 *
 * xPDO is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * xPDO is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * xPDO; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package xpdo-test
 */
/**
 * Tests related to basic xPDOObject methods
 *
 * @package xpdo-test
 * @subpackage xpdo
 */
class xPDOObjectTest extends xPDOTestCase {
    /**
     * Setup dummy data for each test.
     */
    public function setUp() {
        parent::setUp();
        try {
            /* ensure we have clear data */
            $this->xpdo->removeCollection('Phone',array());
            $this->xpdo->removeCollection('Person',array());
            $this->xpdo->removeCollection('PersonPhone',array());
            $this->xpdo->removeCollection('BloodType',array());

            $bloodTypes = array('A+','A-','B+','B-','AB+','AB-','O+','O-');
            foreach ($bloodTypes as $bloodType) {
                $bt = $this->xpdo->newObject('BloodType');
                $bt->set('type',$bloodType);
                $bt->set('description','');
                if (!$bt->save()) {
                    $this->xpdo->log(xPDO::LOG_LEVEL_FATAL,'Could not add blood type: '.$bloodType);
                }
            }

            $bloodTypeABPlus = $this->xpdo->getObject('BloodType','AB+');
            if (empty($bloodTypeABPlus)) $this->xpdo->log(xPDO::LOG_LEVEL_FATAL,'Could not load blood type.');

            /* add some people */
            $person= $this->xpdo->newObject('Person');
            $person->set('id',1);
            $person->set('first_name', 'Johnathon');
            $person->set('last_name', 'Doe');
            $person->set('middle_name', 'Harry');
            $person->set('dob', '1950-03-14');
            $person->set('gender', 'M');
            $person->set('password', 'ohb0ybuddy');
            $person->set('username', 'john.doe@gmail.com');
            $person->set('security_level', 3);
            $person->set('blood_type',$bloodTypeABPlus->get('type'));
            $person->save();

            $phone = $this->xpdo->newObject('Phone');
            $phone->set('id',1);
            $phone->fromArray(array(
                'type' => 'work',
                'number' => '555-111-1111',
            ));
            $phone->save();

            $personPhone = $this->xpdo->newObject('PersonPhone');
            $personPhone->fromArray(array(
                'person' => 1,
                'phone' => 1,
                'is_primary' => true,
            ),'',true,true);
            $personPhone->save();

            $person= $this->xpdo->newObject('Person');
            $person->set('id',2);
            $person->set('first_name', 'Jane');
            $person->set('last_name', 'Heartstead');
            $person->set('middle_name', 'Cecilia');
            $person->set('dob', '1978-10-23');
            $person->set('gender', 'F');
            $person->set('password', 'n0w4yimdoingthat');
            $person->set('username', 'jane.heartstead@yahoo.com');
            $person->set('security_level',1);
            $person->set('blood_type',$bloodTypeABPlus->get('type'));
            $person->save();

            $phone = $this->xpdo->newObject('Phone');
            $phone->set('id',2);
            $phone->fromArray(array(
                'type' => 'work',
                'number' => '555-222-2222',
            ));
            $phone->save();

            $personPhone = $this->xpdo->newObject('PersonPhone');
            $personPhone->fromArray(array(
                'person' => 2,
                'phone' => 2,
                'is_primary' => true,
            ),'',true,true);
            $personPhone->save();
        } catch (Exception $e) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, $e->getMessage(), '', __METHOD__, __FILE__, __LINE__);
        }
    }

    /**
     * Remove dummy data prior to each test.
     */
    public function tearDown() {
        try {
            $person = $this->xpdo->getObject('Person',array(
                'username' => 'john.doe@gmail.com'
            ));
            if ($person) $person->remove();
            $person = $this->xpdo->getObject('Person',array(
                'username' => 'jane.heartstead@yahoo.com'
            ));
            if ($person) $person->remove();

            $this->xpdo->removeCollection('BloodType',array());
        } catch (Exception $e) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, $e->getMessage(), '', __METHOD__, __FILE__, __LINE__);
        }
        parent::tearDown();
    }
    
    /**
     * Test saving an object.
     */
    public function testSaveObject() {
        $result= false;
        try {
            $person= $this->xpdo->newObject('Person');
            $person->set('first_name', 'Bob');
            $person->set('last_name', 'Bla');
            $person->set('middle_name', 'La');
            $person->set('dob', '1971-07-22');
            $person->set('password', 'b0bl4bl4');
            $person->set('username', 'boblabla');
            $person->set('security_level', 1);
            $person->set('gender', 'M');
            $result= $person->save();
            $this->xpdo->log(xPDO::LOG_LEVEL_INFO, "Object after save: " . print_r($person->toArray(), true));
        } catch (Exception $e) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, $e->getMessage(), '', __METHOD__, __FILE__, __LINE__);
        }
        $this->assertTrue($result, "Error saving data.");
        $person->remove();
    }

    /**
     * Tests a cascading save
     */
    public function testCascadeSave() {
        $result= false;
        try {
            $person= $this->xpdo->newObject('Person');
            $person->set('first_name', 'Bob');
            $person->set('last_name', 'Bla');
            $person->set('middle_name', 'Lu');
            $person->set('dob', '1971-07-21');
            $person->set('gender', 'M');
            $person->set('password', 'b0blubl4!');
            $person->set('username', 'boblubla');
            $person->set('security_level', 1);

            $phone1= $this->xpdo->newObject('Phone');
            $phone1->set('type', 'home');
            $phone1->set('number', '+1 555 555 5555');

            $phone2= $this->xpdo->newObject('Phone');
            $phone2->set('type', 'work');
            $phone2->set('number', '+1 555 555 4444');

            $personPhone1= $this->xpdo->newObject('PersonPhone');
            $personPhone1->addOne($phone1);
            $personPhone1->set('is_primary', false);

            $personPhone2= $this->xpdo->newObject('PersonPhone');
            $personPhone2->addOne($phone2);
            $personPhone2->set('is_primary', true);

            $personPhone= array($personPhone1, $personPhone2);

            $person->addMany($personPhone);

            $result= $person->save();
        } catch (Exception $e) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, $e->getMessage(), '', __METHOD__, __FILE__, __LINE__);
        }
        $this->assertTrue($result == true, "Error saving data.");
        $this->assertTrue(count($person->_relatedObjects['PersonPhone']) == 2, "Error saving related object data.");
        $person->remove();
    }

    /**
     * Test getting an object by the primary key.
     *
     * @depends testSaveObject
     */
    public function testGetObjectByPK() {
        $result= false;        
        try {
            $person= $this->xpdo->getObject('Person',1);
            $result= (is_object($person) && $person->getPrimaryKey() == 1);
            if ($person) $this->xpdo->log(xPDO::LOG_LEVEL_INFO, "Object after retrieval: " . print_r($person->toArray(), true));
        } catch (Exception $e) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, $e->getMessage(), '', __METHOD__, __FILE__, __LINE__);
        }
        $this->assertTrue($result, "Error retrieving object by primary key");
    }

    /**
     * Test using getObject by PK on multiple objects, including multiple PKs
     */
    public function testGetObjectsByPK() {        
        try {
            $person= $this->xpdo->getObject('Person', 2);
            $phone= $this->xpdo->getObject('Phone', 2);
            $personPhone= $this->xpdo->getObject('PersonPhone', array (
                2,
                2,
            ));
        } catch (Exception $e) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, $e->getMessage(), '', __METHOD__, __FILE__, __LINE__);
        }
        $this->assertTrue($person instanceof Person, "Error retrieving Person object by primary key");
        $this->assertTrue($phone instanceof Phone, "Error retrieving Phone object by primary key");
        $this->assertTrue($personPhone instanceof PersonPhone, "Error retrieving PersonPhone object by primary key");
    }

    /**
     * Test getObjectGraph by PK
     */
    public function testGetObjectGraphsByPK() {        
        //array method
        try {
            $person= $this->xpdo->getObjectGraph('Person', array ('PersonPhone' => array ('Phone' => array ())), 2);
            if ($person) {
                $personPhoneColl= $person->getMany('PersonPhone');
                if ($personPhoneColl) {
                    $phone= null;
                    foreach ($personPhoneColl as $personPhone) {
                        if ($personPhone->get('phone') == 2) {
                            $phone= $personPhone->getOne('Phone');
                            break;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, $e->getMessage(), '', __METHOD__, __FILE__, __LINE__);
        }
        $this->assertTrue($person instanceof Person, "Error retrieving Person object by primary key via getObjectGraph");
        $this->assertTrue($personPhone instanceof PersonPhone, "Error retrieving retreiving related PersonPhone collection via getObjectGraph");
        $this->assertTrue($phone instanceof Phone, "Error retrieving related Phone object via getObjectGraph");
    }

    /**
     * Test getObjectGraph by PK with JSON graph
     */
    public function testGetObjectGraphsJSONByPK() {        
        //JSON method
        try {
            $person= $this->xpdo->getObjectGraph('Person', '{"PersonPhone":{"Phone":{}}}', 2);
            if ($person) {
                $personPhoneColl= $person->getMany('PersonPhone');
                if ($personPhoneColl) {
                    $phone= null;
                    foreach ($personPhoneColl as $personPhone) {
                        if ($personPhone->get('phone') == 2) {
                            $phone= $personPhone->getOne('Phone');
                            break;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, $e->getMessage(), '', __METHOD__, __FILE__, __LINE__);
        }
        $this->assertTrue($person instanceof Person, "Error retrieving Person object by primary key via getObjectGraph, JSON graph");
        $this->assertTrue($personPhone instanceof PersonPhone, "Error retrieving retreiving related PersonPhone collection via getObjectGraph, JSON graph");
        $this->assertTrue($phone instanceof Phone, "Error retrieving related Phone object via getObjectGraph, JSON graph");
    }

    /**
     * Test xPDO::getCollection
     */
    public function testGetCollection() {        
        try {
            $people= $this->xpdo->getCollection('Person');
        } catch (Exception $e) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, $e->getMessage(), '', __METHOD__, __FILE__, __LINE__);
        }
        $this->assertTrue(isset($people[1]) && $people[1] instanceof Person, "Error retrieving all objects.");
        $this->assertTrue(isset($people[2]) && $people[2] instanceof Person, "Error retrieving all objects.");
        $this->assertTrue(count($people) == 2, "Error retrieving all objects.");
    }

    /**
     * Test xPDO::getCollectionGraph
     */
    public function testGetCollectionGraph() {        
        try {
            $people= $this->xpdo->getCollectionGraph('Person', array ('PersonPhone' => array ('Phone' => array ())));
        } catch (Exception $e) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, $e->getMessage(), '', __METHOD__, __FILE__, __LINE__);
        }
        return;
        $this->assertTrue($people[1] instanceof Person, "Error retrieving all objects.");
        $this->assertTrue(isset($people[2]) && $people[2] instanceof Person, "Error retrieving all objects.");
        $this->assertTrue(isset($people[2]) && $people[2]->_relatedObjects['PersonPhone']['2-1'] instanceof PersonPhone, "Error retrieving all objects.");
        $this->assertTrue(isset($people[2]) && $people[2]->_relatedObjects['PersonPhone']['2-1']->_relatedObjects['Phone'] instanceof Phone, "Error retrieving all objects.");
        $this->assertTrue(count($people) == 2, "Error retrieving all objects.");
    }

    /**
     * Test xPDO::getCollectionGraph with JSON graph
     */
    public function testGetCollectionGraphJSON() {        
        try {
            $people= $this->xpdo->getCollectionGraph('Person', '{"PersonPhone":{"Phone":{}}}');
        } catch (Exception $e) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, $e->getMessage(), '', __METHOD__, __FILE__, __LINE__);
        }
        return;
        $this->assertTrue($people[1] instanceof Person, "Error retrieving all objects.");
        $this->assertTrue(isset($people[2]) && $people[2] instanceof Person, "Error retrieving all objects.");
        $this->assertTrue(isset($people[2]) && $people[2]->_relatedObjects['PersonPhone']['2-1'] instanceof PersonPhone, "Error retrieving all objects.");
        $this->assertTrue(isset($people[2]) && $people[2]->_relatedObjects['PersonPhone']['2-1']->_relatedObjects['Phone'] instanceof Phone, "Error retrieving all objects.");
        $this->assertTrue(count($people) == 2, "Error retrieving all objects.");
    }

    /**
     * Test getMany
     * @dataProvider providerGetMany
     * @param string $person The username of the Person to use for the test data.
     * @param string $alias The relation alias to grab.
     */
    public function testGetMany($person,$alias) {
        $person = $this->xpdo->getObject('Person',array(
            'username' => $person,
        ));
        if (!$person) {
            $this->xpdo->log(xPDO::LOG_LEVEL_FATAL,'Could not get Person for testGetMany.');
            return;
        }
        try {
            $personPhones = $person->getMany($alias);

        } catch (Exception $e) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, $e->getMessage(), '', __METHOD__, __FILE__, __LINE__);
        }
        $this->assertTrue(!empty($personPhones),'xPDOQuery: getMany failed from Person to PersonPhone.');
    }
    /**
     * Data provider for testGetMany
     */
    public function providerGetMany() {
        return array(
            array('jane.heartstead@yahoo.com','PersonPhone'),
        );
    }


    /**
     * Test getOne
     * @dataProvider providerGetOne
     * @param string $username The username of the Person to use for the test data.
     * @param string $alias The relation alias to grab.
     */
    public function testGetOne($username,$alias,$class) {
        $person = $this->xpdo->getObject('Person',array(
            'username' => $username,
        ));
        if (!$person) {
            $this->xpdo->log(xPDO::LOG_LEVEL_FATAL,'Could not get Person for testGetOne.');
            return;
        }
        try {
            $one = $person->getOne($alias);

        } catch (Exception $e) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, $e->getMessage(), '', __METHOD__, __FILE__, __LINE__);
        }
        $this->assertTrue(!empty($one) && $one instanceof $class,'xPDOQuery: getMany failed from Person `'.$username.'` to '.$alias.'.');
    }
    /**
     * Data provider for testGetOne
     */
    public function providerGetOne() {
        return array(
            array('jane.heartstead@yahoo.com','BloodType','BloodType'),
        );
    }




    /**
     * Test removing an object
     */
    public function testRemoveObject() {
        $result= false;

        $person = $this->xpdo->newObject('Person');
        $person->set('id',123);
        $person->set('first_name', 'Kurt');
        $person->set('last_name', 'Dirt');
        $person->set('middle_name', 'Remover');
        $person->set('dob', '1978-10-23');
        $person->set('gender', 'F');
        $person->set('password', 'fdsfdsfdsfds');
        $person->set('username', 'dirt@remover.com');
        $person->set('security_level',1);
        $person->save();
        try {
            if ($person= $this->xpdo->getObject('Person', 123)) {
                $result= $person->remove();
            }
        } catch (Exception $e) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, $e->getMessage(), '', __METHOD__, __FILE__, __LINE__);
        }
        $this->assertTrue($result == true, "Error removing data.");
    }

    /**
     * Test removing a dependent object
     */
    public function testRemoveDependentObject() {        
        $result= false;
        $phone = $this->xpdo->newObject('Phone');
        $phone->set('id',123);
        $phone->set('type', 'work');
        $phone->set('number', '555-789-4563');
        $phone->set('is_primary',false);
        $phone->save();
        try {
            if ($phone= $this->xpdo->getObject('Phone', 123)) {
                $result= $phone->remove();
            }
        } catch (Exception $e) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, $e->getMessage(), '', __METHOD__, __FILE__, __LINE__);
        }
        $this->assertTrue($result == true, "Error removing data.");
    }

    /**
     * Test removing circular composites
     */
    public function testRemoveCircularComposites() {        
        $result= false;
        try {
            if ($personPhone= $this->xpdo->getObject('PersonPhone', array (2, 2))) {
                $result= $personPhone->remove();
                unset($personPhone);
                if ($result) {
                    if ($personPhone= $this->xpdo->getObject('PersonPhone', array (2, 2))) {
                        $this->assertTrue(false, "Parent object was not removed.");
                    }
                    if ($phone= $this->xpdo->getObject('Phone', 2)) {
                        $this->assertTrue(false, "Child object was not removed.");
                    }
                }
            }
        } catch (Exception $e) {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, $e->getMessage(), '', __METHOD__, __FILE__, __LINE__);
        }
        $this->assertTrue($result == true, "Error removing objects with circular composite relationships.");
    }
}