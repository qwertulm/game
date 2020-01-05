<?php
include_once './console.php';
$fp = fopen('php://stdin', 'r');

const COLORS = ['blue', 'green', 'cyan', 'red', 'purple', 'brown', 'light_gray'];

class Person {
  private $name;
  private $place;
  
  public function __construct($name) {
      $this->name = $name;
      $this->place = new EmptyPlace();
  }
 
  public function print() {
    $color = $this->get_color();
    Console::log(
        $this->name,
        $color,
        false
    );
  }
  
  private function get_color()
  {
    $sum = 0;

    $array = preg_split('//', $this->name, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($array as $val) {
        $sum += ord($val);
    }
    
    $color_id =  $sum % sizeof(COLORS);
    return COLORS[$color_id];
  }
  
  public function set_place($place) {
    $this->place = $place;
  }
  
  public function get_place() {
    return $this->place;
  }
}

class Place {
  public $id;
  protected $name;
  protected $color;
  
  public function __construct(
    $id,
    $name,
    $color
  ) {
    $this->id = $id;
    $this->name = $name;
    $this->color = $color; 
  }
  
  public function print() {
    Console::log(
        $this->name,
        $this->color,
        false
    );
  }
}

class EmptyPlace extends Place {
  public function __construct() {
    $this->id = 0;
    $this->color = 'purple';
    $this->name = 'ne bylo ego';
  }
}

class PersonProcessor {
  public $pers_free;
  public $pers_in_game;
  public $places;
  
  public function __construct() {
    $this->places = [
      //  new EmptyPlace(),
      new Place(1, 'prishol v batalion', 'green'),
      new Place(2, 'ushol v shtab', 'red'),
      new Place(3, 'ushol v park', 'cyan'),
      new Place(4, 'ushol domoi', 'blue'),

    ];
    
    $this->pers_free = [
      new Person('Chirich'), 
      new Person('Katulskiy' ),
      new Person('Ruban' ), 
      new Person('Ancipovich'), 
      new Person('Cimbaluk'  ),
      new Person('Naruha'),
      new Person('Batia'),
      new Person('Klimovec'),
      new Person('Undel'),
      new Person('Zhdan')
    ];
    
    $this->pers_in_game = [];
  }
  
  public function addPerson() {
    $key = array_rand($this->pers_free);
    $this->pers_in_game[] = $this->pers_free[$key];
    $person = $this->pers_free[$key];
    unset($this->pers_free[$key]);
    $this->setLocation($person);
  }
  
  public function rmPerson() {
    $key = array_rand($this->pers_in_game);
    $this->pers_free[] = $this->pers_in_game[$key];
    $person = $this->pers_in_game[$key];
    unset($this->pers_in_game[$key]);
  }
  
  public function setLocation($person) {
    $place = $this->places[
      array_rand($this->places)
    ]; 
    
    $person->set_place($place);
    $this->printLocation($person);
  }
  
 
  
  public function changeLocation($person) {
    $old_place_key = array_search(
      $person->get_place(),
      $this->places
    );
    
    $new_place_key = rand(0, sizeof($this->places) - 2);
    if ($new_place_key == $old_place_key)
      $new_place_key++;
    
    $place = $this->places[$new_place_key]; 
    
    $person->set_place($place);
    $this->printLocation($person);
  }
  
  public function printLocation($person) {
    $person->print();
    echo ' ';
    $person->get_place()->print();
    echo "\n";
  }
  
  public function printLocations() {
    foreach ($this->pers_in_game as $person) {
      $this->printLocation($person);
    }
  }
  
  public function getRandomPerson() {
    return $this->pers_in_game[
      array_rand($this->pers_in_game)
    ];
  }
  
  public function gameStart() {
    system('clear');
    for ($i=0; $i<5; $i++) {
      $this->addPerson();
    }
  }
  
  public function getPersonsCount() {
    return sizeof($this->pers_in_game);
  }
  
  public function persInPlace($place) {
    $ans = 0;
    foreach ($this->pers_in_game as $pers) {
      if ($pers->get_place() == $place) {
        $ans++;
      }
    }
    return $ans;
  }
}

class AskProcessor {
  public $results;
  
  public function __construct() {
    $this->results = [];
  }
  
  public function addResult($result) {
    $this->results[] = $result;
  }
 
  
  
  public function printResults() {
    $consoleWidth = exec('echo $COLUMNS') ?? 20;
    $start = sizeof($this->results) - 1;
    $end = max(0, $start - $consoleWidth + 1);
  
    for ($i=$start; $i>=$end; $i--) {
      if ($this->results[$i]) {
        $color = 'green';
      } else {
        $color = 'red';
      }
      Console::log(
        '■', 
        $color, 
        false
      );
    }
    echo "\n";
  }
  
  public function whereIs($person, $persPr) {
        echo 'where is ';
        $person->print();
        echo "?\n";
        foreach ($persPr->places as $place) {
            echo $place->id . '. ';
            $place->print();
            echo "\n";
        }
        global $fp;
        $ans = trim(fgets($fp, 1024));
        $result = ($ans == $person->get_place()->id);
        if ($result) {
            Console::log('True', 'green');
        } else {
            Console::log('False, ', 'red', false);
            $persPr->printLocation($person);
            
        }
        $this->addResult($result);

        
  }
  
  public function howMany($place, $persPr) {
    echo 'how many people ';
    $place->print();
    echo "\n";
    global $fp;
    $ans = trim(fgets($fp, 1024));
    $result = ($ans == $persPr->persInPlace($place));
    if ($result) {
            Console::log('True', 'green');
        } else {
            Console::log('False, ' . $persPr->persInPlace($place), 'red', false);
           
            
        }
        $this->addResult($result);

    
  }
  
  
}




$persPr = new PersonProcessor();
$persPr->gameStart();
$askPoss = 1;
$askPr = new AskProcessor();
$stepCount = 0;
while (true) {
    $line = fgets($fp, 1024);
    if (".\n" == $line) {
        break;
    }
    
    if ("?\n" == $line) {
        $persPr->printLocations();
        continue;
    }
    
    if ("add\n" == $line) {
        $persPr->addPerson();
    }
    
    if ("rm\n" == $line) {
        $persPr->rmPerson();
    }
    
    system('clear');
    $askPr->printResults();
    echo (
      'st: ' . $stepCount .
      ' ps: ' . $persPr->getPersonsCount() . "\n"
    );
    
    $person = $persPr->getRandomPerson();    
       
    $ask = rand(0,$askPoss) == 0;
    if ($ask) $askPoss = 4; else $askPoss--;
    
    if ($ask) { 
      if (rand(0,0) == 0) {
        $askPr->howMany(
          $person->get_place(),
          $persPr
        );
      } else {
        $askPr->whereIs($person, $persPr);
      }
       
        
    } else {
      $persPr->changeLocation($person);
      $stepCount++;
    }

}
    
