<?php
// Instantiate the class responsible for implementing a micro application
$app = new \Phalcon\Mvc\Micro();

// Routes
$app->get('/', 'home');
$app->get('/api', 'home');	
$app->get('/api/movie/{title}', 'findMovie'); // curl -i -X GET http://host/Neo4jMovies/api/api/movie/The%20Matrix
$app->get('/api/actor/{name}', 'findActor');  // curl -i -X GET http://host/Neo4jMovies/api/api/actor/Keanu%20Reeves
$app->get('/api/filmography/{name}', 'findFilmography'); // curl -i -X GET http://host/Neo4jMovies/api/api/filmography/Keanu%20Reeves
$app->notFound('notFound');

// Handlers

// Show the use of the API
function home() {
	header('Location:../useOfTheAPI.php');
}

// Returns title, released, casting, directors and producers of the movie title passed as parameter
// Example:
// {"title":"The Matrix",
//  "released":1999,
//  "tagline":"Welcome to the Real World",
//  "cast":[
//		{"name":"Keanu Reeves","born":1964},
//		{"name":"Carrie-Anne Moss","born":1967},
//		{"name":"Laurence Fishburne","born":1961},
//		{"name":"Hugo Weaving","born":1960},
//		{"name":"Emil Eifrem","born":1978}
//	],
//	"director":[
//		{"name":"Andy Wachowski","born":1967},
//		{"name":"Lana Wachowski","born":1965}
//	],
//	"producer":[
//		{"name":"Joel Silver","born":1952}
//	]
// }
function findMovie ($title) {

	// Create the connection
	include("../connection.php");

	// Setup a query that return the movie and the path (movie-rel->person) of the movie with persons for all the relations
	$cypher = "match(m:Movie) WHERE m.title = '".$title."' RETURN m";


	// Run the query
	$params = array("the_title" => $title);
	//var_dump($params);
	$res = $connection->sendCypherQuery($cypher,$params)->getResult();
	//var_dump($res);
	// Obtain the movie nodes from the result
	$movie = $res->getSingleNode();
	//var_dump($movie);

	// Setup on the array of results the values of title, released and tagline of the movie
	$data['title'] = $movie->getProperty('title');
	$data['released'] = $movie->getProperty('released');
	$data['tagline'] = $movie->getProperty('tagline');

	
	// 1. Obtaing the cast of the movie
	// Obtain the relationships ACTED_IN from the resultset
	$actorRels = $movie->getRelationships('ACTED_IN');
	$actorsArray=array();
	// Build and array of actors iterating through the relations getting the start node properties of each relation
	foreach ($actorRels as $actorRel) {
		$actor = $actorRel->getEndNode();
		$theActor['name'] = $actor->getProperty('name');
		$theActor['born'] = $actor->getProperty('born');

		

		$actorsArray[] = $theActor;
	}

	// Add to the result array the obtained casting 
	$data['cast'] = $actorsArray;

	// 2. Obtaing the directors of the movie 
	// Obtain the relationships DIRECTED from the resultset
	
	$directors = $movie->getRelationShips('DIRECTED');
	$directorsArray=array();
	// Build and array of directors iterating through the relations getting the start node properties of each relation
	foreach ($directors as $director) {
		$persondirector = $director->getEndNode();
		$theDirector['name'] = $persondirector->getProperty('name');
		$theDirector['born'] = $persondirector->getProperty('born');

		/**********
		*** YOUR CODE HERE
		**********/

		$directorsArray[] = $theDirector;
	}

	// Add to the result array the obtained directors 
	$data['director'] = $directorsArray;

	// 3. Obtaing the producers of the movie
	// Obtain the relationships PRODUCED from the resultset
	/**********
	*** YOUR CODE HERE
	**********/
	$producers = $movie->getRelationShips('PRODUCED');
	$producerArray=array();
	// Build and array of producers iterating through the relations getting the start node properties of each relation
	foreach ($producers as $producer) {
		$prod = $producer->getEndNode();
		$theProducer['name'] = $prod->getProperty('name');
		$theProducer['born'] = $prod->getProperty('born');

		/**********
		*** YOUR CODE HERE
		**********/

		$producerArray[] = $theProducer;
	}

	// Add to the result array the obtained producers
	$data['producer'] = $producerArray;

	// Return the result as JSON
	echo json_encode($data);

}

// Returns name and born year of the actor passed as parameter
// Example
// {"name":"Keanu Reeves","born":1964}
function findActor ($name) {

	// Create the connection
	include("../connection.php");

	// Setup a query that return the person 
	$cypher = "match(p:Person) WHERE p.name='".$name."' RETURN p";
	
	// Run the query
	$params=array('the_name' => $name);
	$res = $connection->sendCypherQuery($cypher,$params)->getResult();


	//Obtain the node returned by the query from the resultset
	$person=$res->getSingleNode();

	// Add to the result array the name and the born year of the actor
	$data['name'] = $person->getProperty('name');
	$data['born'] = $person->getProperty('born');


	// Return the result as JSON
	echo json_encode($data);

}

// Returns name, born year and filmography of the actor passed as parameter
// Example:
// {"name":"Keanu Reeves",
//	"born":1964,
//	"filmography":[
//		{"title":"The Matrix","released":1999},
//		{"title":"The Matrix Reloaded","released":2003},
//		{"title":"The Matrix Revolutions","released":2003},
//		{"title":"The Devil's Advocate","released":1997},
//		{"title":"The Replacements","released":2000},
//		{"title":"Johnny Mnemonic","released":1995},
//		{"title":"Something's Gotta Give","released":1975}
//	]
// }
function findFilmography ($name) {

	// Create the connection
	include("../connection.php");

	// Setup a query that return the movie and the path (movie-[:ACTED_IN]->person) of the movie with all its actors
	$cypher = "match(p:Person) WHERE p.name='".$name."' RETURN p";

	//Run the query and assign the result
	$res = $connection->sendCypherQuery($cypher)->getResult();

	// Obtain the movie path from the resultset
	$person = $res->getSingleNode();

	// Add to the result array the name and the born year of the actor
	$data['name'] = $person->getProperty('name');
	$data['born'] = $person->getProperty('born');

	/**********
	*** YOUR CODE HERE
	**********/

	// Obtain the relationships ACTED_IN from the resultset
	$movieRels = $person->getRelationShips('ACTED_IN');
	$movieArray=array();
	// Build and array of movies iterating through the relations getting the end node properties of each relation
	foreach ($movieRels as $movieRel) {
		$themovie = $movieRel->getEndNode();
		$theMovie['title'] = $themovie->getProperty('title');
		$theMovie['released'] = $themovie->getProperty('released');

		/**********
		*** YOUR CODE HERE
		**********/

		$movieArray[] = $theMovie;
	}

	// Add to the result array the set of obtained movies
	$data['filmography'] = $movieArray;

	// Return the result as JSON
	echo json_encode($data);

}


function notFound() {
	home();
}

// Handle the request
$app->handle();
?>

