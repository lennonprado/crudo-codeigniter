# crudo-codeigniter
CRUD for Codeigniter

<p>Creating CRUD grid is a very common task in web development (CRUD stands for Create/Read/Update/Delete). If you are a web developer, you must have created plenty of CRUD grids already. They maybe exist in a content management system, an inventory management system, or accounting software.</p>

<p>The main purpose of a CRUD is that enables users create/read/update/delete data. Normally data is stored in MySQL Database. PHP will be the server-side language that manipulates MySQL Database tables to give front-end users power to perform CRUD actions.</p>

<p>Whit this repository, we will create fastly PHP CRUD in Codeigniter framework. </p>

<p>CodeIgniter is a powerful PHP framework with a very small footprint, built for developers who need a simple and elegant toolkit to create full-featured web applications.</p>

<h1>How to use<h1>

- Set constants in index file
    
    define('BASEURL', 'http://tusubdominio.dominio.com/');
	define('CLIENTE', 'Title App');
	define('ABSOLUTEURL', 'http://tudominio.com/');
	define('LOGO', 'urllogo.jpg'); // not necesary
	define('ENVIRONMENT', 'development');
	define('TITULO_LOGIN', 'Login Title');

- Set database config files in /aplication/config/

    // hostname 
    $db['default']['hostname'] = '127.0.0.1';
    // db username
    $db['default']['username'] = '';
    // db password
    $db['default']['password'] = '';
    // database
    $db['default']['database'] = '';    

- run the aplication


<h1>How to works</h1>

<p>Crudoyqueso controller list databases.</p>
<p>Select the database to map and then select the table to create respective views, controller and models.</p>
<p>All files will be included in their respective folder</p>
<p>The name of the selected table are the name of file and class</p>
<p>Is required for correctly work, include the models created in your respective config file. This file is in /config/autoload.php</p>


