<?php

namespace go\modules\community\music\controller;

use go\core\cli\Controller;
use go\core\db\Criteria;
use go\core\db\Expression;
use go\core\db\Query;
use go\core\util\DateTime;
use go\modules\community\music\model\Album;
use go\modules\community\music\model\Artist;

class CliDemo extends Controller {
	public function hello($name = "World") {
		echo "Hello $name!\n";
	}
	
	public function select() {
		
		//Build new select query object
		$query = (new Query())
						->select('*')
						->from('music_artist')						
						->orderBy(['name' => 'ASC']);
		
		//Query objects can be stringified for debugging
		echo $query . "\n";
		
		$this->printQuery($query);
		
		
		//Quick way to fetch single result
		$record = (new Query)
						->select('id, name')
						->from('music_artist')
						->single();
		
		var_dump($record);
	}
	
	private function printQuery(Query $query) {
		//Query objects are traversable, but you can also use $query->single() to 
		//fetch a single record or $query->all() to fetch all in an array.		
		foreach($query as $record) {			
			foreach($record as $key => $value) {
				echo $key . ": " . var_export($value, true) . "\n";
			}
			echo "-----\n";
		}
	}
	
	public function join() {
		// Join example with aliases for the table names
		$query = (new Query)
						->select('art.id, art.name, count(alb.id) as albumCount')
						->from('music_artist', 'art')
						->join('music_album', 'alb', 'art.id = alb.artistId')
						->groupBy(['art.id']);
						
		
		$this->printQuery($query);
	}
	
	public function where() {
		
		//Simple where example
		$query = (new Query)
						->select('id, name')
						->from('music_artist')
						->where('name', '=' , 'The Doors');
		
		//The same query can be written as:
		$query = (new Query)
						->select('id, name')
						->from('music_artist')
						->where(['name' => 'The Doors']);
		
		//Or you can work with raw strings:
		$query = (new Query)
						->select('id, name')
						->from('music_artist')
						->where('name = :name')
						->bind(['name' => 'The Doors']);
		
		$this->printQuery($query);
		
		
		
		
		
		//Array values are automatically processed as IN conditions:		
		$query = (new Query)
						->select('id, name')
						->from('music_artist')
						->where(['name' => ['The Doors', 'The war on drugs']]);
		
		echo $query . "\n";
		
		
		//parameter grouping
		$query = (new Query)
						->select('id, name')
						->from('music_artist')
						// Select only artists that were created in the past 3 hours.
						->where('createdAt', '>=', new DateTime("-3 hours"))						
						->andWhere(
										// This will become a grouped where condition between parenthesis
										(new Criteria())
										->where(['name' => 'The Doors'])
										->orWhere(['name' => 'The war on drugs'])
										);
		
		echo $query . "\n";
		
		$this->printQuery($query);
		
		
		//Subquery with EXISTS		
		$query = (new Query)
						->select('id, name')
						->from('music_artist', 'art')
						->whereExists(
										(new Query)
										->select('*')
										->from('music_album', 'alb')
										->where('alb.artistId = art.id')
										);
										
		echo $query . "\n";
		
		$this->printQuery($query);
		
		//Subquery with IN
		
		$query = (new Query)
						->select('id, name')
						->from('music_album', 'alb')
						->where('artistId', 'IN',
										(new Query)
										->select('id')
										->from('music_artist', 'art')
										->where('art.createdAt', '>' , new DateTime("-3 hours"))
										);
										
		echo $query . "\n";
		
		$this->printQuery($query);
		
		
		
	}
	
	
	public function expressions() {
		
		// Sometimes it's useful to pass raw expressions to the query.
		$query = (new Query)
						->select('art.id, art.name, count(alb.id) as albumCount')
						->from('music_artist', 'art')
						->join('music_album', 'alb', 'art.id = alb.artistId')
						->groupBy(['art.id'])
						->orderBy([new Expression("count(alb.id) DESC")]);						
		
		$this->printQuery($query);
	}


	public function find() {
		$artist = Artist::find()->single();
		echo json_encode($artist, JSON_PRETTY_PRINT);

	}
	
	public function create() {
		
		$artist = new Artist();
		$artist->name = "The Doors";
		$artist->albums[] = (new Album())->setValues(['name' => 'The Doors', 'releaseDate' => new DateTime('1968-01-04'), 'genreId' => 2]);
		
		if(!$artist->save()) {
			echo "Save went wrong: ". var_export($artist->getValidationErrors(), true) . "\n";
		} else
		{
			echo "Artist saved!\n";
		}
		
		
		//or use setValues
		
		$artist = (new Artist)
						->setValues([
								'name' => 'The War On Drugs',
								'albums' => [
										['name' => 'Album 1', 'releaseDate' => new DateTime('2018-01-04'), 'genreId' => 2],
										['name' => 'Album 2', 'releaseDate' => new DateTime('2018-01-04'), 'genreId' => 2]
								]
						]);
		
		if(!$artist->save()) {
			echo "Save went wrong: ". var_export($artist->getValidationErrors(), true) . "\n";
		} else
		{
			echo "Artist saved!\n";
		}
	}
}
