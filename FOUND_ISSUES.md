# Issues Found (Inefficient design practices)
These are the issues I feel are not properly implemented as they have obviously defiled good design practices and principles.

## Absence of Migrations (Architectural flaw)
The database table definitions are define in "docker/php/mysql/init.sql", which will not scale the application.
One of the advantages of migrations is schema versioning, which helps better collaborations among team members. We need to add migratons for better database management. For instance, if we have to edit a column in the database, we would have to login to MYSQL cli/client to make an edit. This will definately lead to in-consistency as the other team members wouldn't be aware of the schema modification and versions.

## Non-centralized DB connection (Design flaw)
The database connection is defined in src/Repository/TransactionRepository, which would always create a new PDO instance, whenever the TransactionRepository class is instantiated. This will lead to in-flexibility, as it would be difficult to use a different database (e.g Mongo) on the project. Another disadvantage is caching as the DB connection cannot be cached for the existing code implementation.  An example below demostrates the flaw:

```bash
    public function __construct()
    {
        $this->pdo = new PDO('mysql:host=db;dbname=my_budget', 'root', 'root'); //In-efficient implemetation
    }
```

A good implementation is to define the DB connection in an environment file (.env). This will helps caching and flexibilty in the choice of Database. An example below demonstrates the proposed solution:

```bash
    This should be placed in a .env file
    DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7"
```

## No Dependency Injection in controller (design flaw)
The existing implenetation is tightly coupled to depend on an instance of Transaction repository, which is in-efficient as a new Object of TransactionRepository has to be created everytime the controller is called. An example below demostrates the flaw:

```bash
    public function __construct()
    {
        $this->transactionRepository = new TransactionRepository(); //In-efficient implemetation
    }
```

Ideally, we are meant to inject controllers' dependencies, which are resolved and cached by the service container. This will boost the application speed and support more abstraction and extensibility. An example below demonstrates the proposed solution:

```bash
    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository; //efficient implemetation
    }
```

## Quering Database table values (Design flaw)
The existing implementation uses direct PDO queries to get table values. This has tightly coupled the application, hence not allowing extensibility. If there's a need to use a different DB, the code would have to re-written. An example below demostrates the flaw:

```bash
    $this->pdo->query("SELECT SUM(amount) FROM transactions")->fetchColumn(); //In-efficient implemetation
```

A better implemenation is to use an entity model repository, which is an ORM implementation active records. This would abstract the PDO implementation details, thus foster flexibility and extensibility, An example below demonstrates the proposed solution:

```bash
    $this->createQueryBuilder('a')  //Efficient implemetation
        ->select('SUM(a.amount)')
        ->getResult(); 
```

## Absence of Entity mutators (Design flaw)
The existing implemetation of models/entity has coupled the attributes/table-columns as constructor arguments, this is a bad design for scaling as new arguments will be added to the constructor whenever we add an new column to the table. An example below demonstrates the design flaws:

```bash
    public function __construct(?int $id, string $title, float $amount, DateTime $createdAt = null)
    {
        $this->id = $id;
        $this->title = $title;          //In-efficient implemetation
        $this->amount = $amount;
        $this->createdAt = $createdAt;
    }
    We would have to add a new argument when our table has a new column
```

A better implementation would be to have setters(mutuators) and getters(which already exists), to have a better abstration.

```bash
    private $amount;

    public function __construct()
    {
        //We don\'t have to add a new argument to the constructor when our table has a new column
    }

    //getter
    Public function getAmount($amount)
    {
        return $this->amount;
    }

    //mutator/setter
    Public function setAmount($amount)
    {
        $this->amount = $amount;
    }
    NOTE; You need to install DOctrine ORM mapping implementation for this to work properly
```

## Multiple responsibilities for controller action (Design flaw)
1. A controller action meant to recieve a request, pass it the handler/repository and return a response. In TransactionController "insert" method, the responsibilty is extended from taking the request, to creating an instance of Transaction and finally returning a response.

```bash
    public function insert(Request $request): JsonResponse
    {
        $parameters = json_decode($request->getContent());

        $transaction = new Transaction(
            null,
            $parameters->title,
            $parameters->amount
        );                                    //Not utilizing single responsibility prinsiple

        $balance = $this->transactionRepository->getBalance() + $parameters->amount;

        $transaction = $this->transactionRepository->insert($transaction);

        return new JsonResponse([
            'id' => $transaction->getId(),
            'title' => $parameters->title,
            'amount' => $parameters->amount,
            'createdAt' => $transaction->createdAt()->format(DATE_ATOM),
            'balance' => $balance,
        ]);
    }
```

A better implementation would be to make the controller as lean as possible

```bash
    //Controller Utilizing Single responsibility principle
    public function insert(Request $request): JsonResponse
    {
        $parameters = json_decode($request->getContent());

        $transaction = $this->transactionRepository->insert($parameters);

        return new JsonResponse($transaction);
    }

    //Repository Utilizing Single responsibility principle
    public function insert(Object $parameters): JsonResponse
    {
        $transaction = new Transaction();

        $transaction->setTitle($parameter->title);
        $transaction->setAmount($parameter->amount);

       //persit to db
       //flush/insert

       return [
           'id','title','amount','balance' // this is for brevity
       ]
    }
```

2. A method should explicitly be responsible for performing an operation. The existing implementation's         TransactionController "insert" serves dual purpose (debit and credit). This should seperated into two seperate methods (credit and debit) to adhere to the single responsibility principle.

## Non-Semantic naming for controller methods (Design flaw)
Naming conventions are part of software desing best practices as they help other developers during collaboration.
It also make you write less comments as your mathod name is descriptive enough. The existing implemetation uses method "insert" for both debit and credit transactions, this is not explicit enough. Having seperate methods for credit and debit transactions would boost the sematics to the application. Below is an example of the design flaw

```bash
   public function insert()
   {
       // this method name could mean anything
   }
```
A better name could be

```bash
   public function credit()
   {
       // this method name denotes a credit transaction
   }
   public function debit()
   {
       // this method name denotes a debit transaction
   }
```

## Absence of Request validation (Design flaw)
Request parameters should be validated at the controller level before passing them to the mode/Entity. Validation wasn't considered in TransactionController "insert" method. An example of the flaw is described below.

```bash
    public function insert(Request $request): JsonResponse
    {
        $parameters = json_decode($request->getContent());

        ///.......
    }
```
A better implementation could be

```bash
    public function insert(Request $request,ValidatorInterface $validator): JsonResponse
    {
        $parameters = json_decode($request->getContent());

        $transaction = new Transaction();
        $transaction->setTitle($parameters->title);
        $transaction->setAmount($parameters->amount);

        $errors = $validator->validate($transaction);

        if (count($errors) > 0) {
            return new Response((string) $errors, 442);
        }

        ///.......
    }
```
## Invalid cache reference (Design error/flaw)
There is an invalid reference to Redis cache in TransactionRepository "insert" method. This would always return an invalid balance. Below is a decripton of the flaw

```bash
    public function insert(Transaction $transaction): Transaction
    {
        $this->pdo->exec("
            INSERT INTO transactions (`title`, `amount`)
            VALUES ('{$transaction->getTitle()}', {$transaction->getAmount()});
        ");

        $this->cache->del('all_transactions', 'balanse'); // invalid reference (balance)

        ///......
    }
```
The simple fix is

```bash
    public function insert(Transaction $transaction): Transaction
    {
        $this->pdo->exec("
            INSERT INTO transactions (`title`, `amount`)
            VALUES ('{$transaction->getTitle()}', {$transaction->getAmount()});
        ");

        $this->cache->del('all_transactions', 'balance');

        ///......
    }
```

## Absence of test Database (Design error/flaw)
It is always a good practice to have a seperate database for testing. Doctrine also encourages this methodology by using a test database that is different from the main database. Everytime we run our tests, we overide our main database, which might result is loss of data. From the Behat "FeatureContext", the main database is referenced. Below is a descripton of the flaw:

```bash
    $pdo = new PDO('mysql:host=db;dbname=my_budget', 'root', 'root');
```
We could create a test DB and have it referenced here, hence seperating our test and actual data
```bash
    $pdo = new PDO('mysql:host=db;dbname=my_budget_test', 'root', 'root'); // uses test DB
```

