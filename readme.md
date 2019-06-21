## Building an SMS Based Product Verification Application with Laravel
In this tutorial, we will teach you how to use [Twilio’s Programmable SMS](https://www.twilio.com/sms) to create an SMS based product verification application using [Laravel](https://laravel.com/). After we’re finished, you will have developed a custom SMS verification system that allows your users to check the authenticity of a product via SMS.

## Prerequisites 

In order to follow this tutorial, you will need:

- Basic knowledge of Laravel
- [Laravel](https://laravel.com/docs/master) Installed on your local machine
- [Composer](https://getcomposer.org/) globally installed
- [MySQL](https://www.mysql.com/downloads/) setup on your local machine
- [Twilio Account](https://www.twilio.com/try-twilio) 


## Getting Started

We will start off by creating a new Laravel project. This can be done either using the [Laravel installer](https://laravel.com/docs/5.8#installation) or [Composer](https://getcomposer.org/). We will be making use of the Laravel installer in this tutorial. If you don’t have it installed, you can check how to set it up from the [Laravel documentation](https://laravel.com/docs/master). 
To generate a fresh Laravel project, let’s run the laravel command on our terminal:

    $ laravel new sms-verify

Let’s proceed to install the [Twilio SDK](https://www.twilio.com/docs/libraries/php) for PHP. Change your working directory to the new project generated `sms-verify` and install the Twilio SDK via composer:

    $ cd sms-verify
    $ composer require twilio/sdk 

If you don’t have Composer installed on your local machine you can do so by following the instructions in [their documentation](https://getcomposer.org/doc/00-intro.md).

### Setting up the Twilio SDK
We have successfully installed the [Twilio SDK](https://www.twilio.com/docs/libraries), in order to make use of it we need to get our Twilio credentials from the Twilio dashboard. Head over to your [dashboard](https://www.twilio.com/console) and grab your `account_sid` and `auth_token`.

![](https://paper-attachments.dropbox.com/s_F7BA2EF37979C4BF44B5AA1B9207D8D3EC9EDDE27FB9D710DDC99DD2BCB47338_1560580778216_Group+6.png)


Now navigate to the [Phone Number](https://www.twilio.com/console/phone-numbers/incoming) section to get your SMS enabled phone number.

![](https://paper-attachments.dropbox.com/s_F7BA2EF37979C4BF44B5AA1B9207D8D3EC9EDDE27FB9D710DDC99DD2BCB47338_1560580966888_Group+2+2.png)


If you don’t have an active number, you can easily create one [here](https://www.twilio.com/console/phone-numbers/search). This is the phone number we will be making use of for sending and receiving SMS via Twilio. 

Let's update our `.env` file with our Twilio credentials. Open  `.env` located at the root of the project directory and add these values:

    TWILIO_SID="INSERT YOUR TWILIO SID HERE"
    TWILIO_AUTH_TOKEN="INSERT YOUR TWILIO TOKEN HERE"
    TWILIO_NUMBER="INSERT YOUR TWILIO NUMBER IN [E.164] FORMAT"


## Setup the Database

At this point, we have successfully setup our Laravel project with the Twilio SDK installed. We can now proceed to setting up our database for this project.  If you use a MySQL client like [phpMyAdmin](https://www.phpmyadmin.net/) to manage your database then go ahead and create a database named `sms_verify` and skip this section. If not, then install MySQL from the [official site](https://www.mysql.com/downloads/) for your platform of choice. After successful installation, fire up your terminal and run this command to login to MySQL.

    $ mysql -u {your_user_name}

**NOTE:** *******Add the* `*-p*` *flag if you have a password for your mysql instance.
Once you are logged in, run the following command to create a new database*

    mysql> create database sms_verify;
    mysql> exit;

Proceed to change our database configuration accordingly in the `.env` file at the root of our project folder

    DB_DATABASE=sms_verify
    DB_USERNAME=root
    DB_PASSWORD=

### Create Migration
We have successfully created our database, now let’s create our [migration](https://laravel.com/docs/5.8/migrations).  `cd` into the project root directory and run this command:

    $ php artisan make:model Product --migration

This will generate an [eloquent model](https://laravel.com/docs/5.8/eloquent) named `Product` along side a migration file `{current_time_stamp}_create_products_table` in the `/database/migrations` directory.

Now, open up the project folder in your favourite IDE/text editor so that we can begin making changes as needed. Open the newly created migration file and verify that we have same content as this:

![View of just created migration file](https://paper-attachments.dropbox.com/s_F7BA2EF37979C4BF44B5AA1B9207D8D3EC9EDDE27FB9D710DDC99DD2BCB47338_1560584088639_Screenshot+from+2019-06-15+08-34-34.png)


Let’s update the `up()` method with fields needed for the application. Make the following changes to the `up()` function:

    public function up()
        {
            Schema::create('products', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string("name");
                $table->string("product_id");
                $table->boolean("is_original");
                $table->timestamps();
            });
        }

Now that we have added the needed fields for our application, let’s run our migration. Run the following command in the terminal:

    $ php artisan migrate

If the file migrated successfully, we will see the file name `{time_stamp}_create_products_table` printed out in the terminal.

### Seeding the Database
Next we need to setup [seeders](https://laravel.com/docs/5.8/seeding) for our database. This will help us generate dummy products for our application. To do this, let’s generate a seeder class using the `artisan` command:

    $ php artisan make:seeder ProductTableSeeder

Now, open up the just generated `ProductTableSeeder` file and make the following changes:

    <?php
    use Illuminate\Database\Seeder;
    class ProductTableSeeder extends Seeder
    {
        /**
         * Run the database seeds.
         *
         * @return void
         */
        public function run()
        {
            DB::table('products')->insert([
                [
                    'name' => "Product 1",
                    'is_original' => false,
                    'product_id' => "prod_1",
                ],
                [
                    'name' => "Product 2",
                    'is_original' => true,
                    'product_id' => "prod_2",
                ],
                [
                    'name' => "Product 3",
                    'is_original' => false,
                    'product_id' => "prod_3",
                ],
                [
                    'name' => "Product 4",
                    'is_original' => true,
                    'product_id' => "prod_4",
                ],
            ]);
        }
    }
     

This will create four dummy products in our database which will serve as our products for this application. Now open up `DatabaseSeeder` and make the following changes:

    <?php

    use Illuminate\Database\Seeder;

    class DatabaseSeeder extends Seeder
    {
        /**
         * Seed the application's database.
         *
         * @return void
         */
        public function run()
        {
            $this->call(ProductTableSeeder::class);
        }
    }
    

Now let’s run our seeders by running the following command:

    $ php artisan db:seed

After running the above command we should have four products in our database.


## Verifying Products

Now that we have our database all setup and with some sample products, we are ready to write our verification logic. Open up your terminal and run the following command to generate a [controller](https://laravel.com/docs/5.8/controllers) which will hold the logic for our product verification:

    $ php artisan make:controller ProductController

Open up the `ProductController.php` file and add the following function:

    /**
     * Verify a product.
     *
     * @param  Request  $request
     * @return Response
     */
    public function verify(Request $request)
    {
        $from = $request->input("From");
        $body = $request->input("Body");
        $product = Product::where("product_id", $body)->first();
        if (!$product) {
            $product_status = "Product not available";
        } else if ($product->is_original) {
            $product_status = "Original '$product->name', BUY NOW!!";
        } else {
            $product_status = "Fake '$product->name', DON'T BUY!!";
        }
    }

This is the method in charge of verifying the `product_id` sent to us through SMS. Upon receipt, we query our database for a product with the `product_id` sent through the SMS `body`. Depending on the result of the query, we set a message in `$product_status` to be sent back to the user.

### Sending Back a Response
After checking for the product authenticity we need to send a response back to our user. To do this, we will make use of the [Twilio Programmable SMS API](https://www.twilio.com/docs/sms). Let’s create a helper function that will take in needed parameters to send out an SMS. In the `ProductController` add the following method:

      /**
         * Sends sms to user using Twilio's programmable sms client
         * @param String $message Body of sms
         * @param Number $recipients string or array of phone number of recepient
         */
        private function sendMessage($message, $recipients)
        {
            $account_sid = getenv("TWILIO_SID");
            $auth_token = getenv("TWILIO_AUTH_TOKEN");
            $twilio_number = getenv("TWILIO_NUMBER");
            $client = new Client($account_sid, $auth_token);
            $client->messages->create($recipients, 
                    array('from' => $twilio_number, 'body' => $message));
        }

This function takes in two parameters, `$message` (the response to be sent to the user) and `$recipients` (the user’s phone number which sent the initial text). We then proceed to create a new instance of the Twilio SDK client using the Twilio credentials we stored earlier in our `.env` file. After which, we proceed to send the SMS by calling the `create` method of the Twilio client.

    $client->messages->create( $recipients, array(
        'from' => $twilio_number, 
        'body' => $message
    ));

The Twilio `messages→create()` function takes in two parameters of either a receiver or array of receivers of the message and an array with the properties of `from` and `body` where `from` is your active Twilio phone number. Next, make the following changes to the `verify` method:
 
        /**
         * Verify a product.
         *
         * @param  Request  $request
         * @return Response
         */
        public function verify(Request $request)
        {
            $from = $request->input("From");
            $body = $request->input("Body");
            $product = Product::where("product_id", $body)->first();
            if (!$product) {
                $product_status = "Product not available";
            } else if ($product->is_original) {
                $product_status = "Original '$product->name', BUY NOW!!";
            } else {
                $product_status = "Fake '$product->name', DON'T BUY!!";
            }
            return $this->sendMessage($product_status, $from);
        }

We add the `sendMessage` method after checking the status of the product.

## Creating our Routes

We have successfully created our controller functions but need to create routes to access them. Let’s add our route to the application by opening `routes/web.php` and make the following changes:

    <?php
    /*
    |--------------------------------------------------------------------------
    | Web Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register web routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | contains the "web" middleware group. Now create something great!
    |
    */
    Route::get('/', function () {
        return view('welcome');
    });
    
    Route::post('/verify', "ProductController@verify")

So that our request is not blocked by [CSRF verification](https://laravel.com/docs/5.8/csrf) we have to add our route to the `except` array in `app/Http/Middleware/VerifyCsrfToken.php`. Open up the `VerifyCsrfToken.php` and make the following change:

    <?php
    namespace App\Http\Middleware;
    use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
    class VerifyCsrfToken extends Middleware
    {
        /**
         * Indicates whether the XSRF-TOKEN cookie should be set on the response.
         *
         * @var bool
         */
        protected $addHttpCookie = true;
        /**
         * The URIs that should be excluded from CSRF verification.
         *
         * @var array
         */
        protected $except = [
            "/verify"
        ];
    }


## Setting up Twilio Webhook For Responding To SMS 

To enable us respond to messages sent to us via our Twilio phone number, we have to properly configure our Twilio phone number to handle incoming SMS messages and there are [several ways](https://support.twilio.com/hc/en-us/articles/223136047-Configuring-Phone-Numbers-to-Receive-and-Respond-to-SMS-and-MMS-Messages) this can be done depending on your need. For our application, we will make use of [webhooks](https://www.twilio.com/docs/glossary/what-is-a-webhook). Here's a quick look into how Twilio webhooks work:

> Twilio uses webhooks to let your application know when events happen, such as receiving an SMS message or getting an incoming phone call. When the event occurs, Twilio makes an HTTP request (usually a [POST or a GET](https://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol#Request_methods)) to the URL you configured for the webhook.

Now that we have cleared what webhooks are let’s get down to business.

### Exposing Our Server To The Internet
We have to expose our Laravel application to the internet in order to allow remote access from outside of our local machine. To accomplish this, we will make use of [ngrok](https://ngrok.com/).

> ngrok allows you to expose a web server running on your local machine to the internet

If you don’t have [ngrok](https://ngrok.com/) set up on your computer, head over to their [official download page](https://ngrok.com/download) and follow the instructions to get it installed on your machine. If you already have it set up then open up your terminal and run the following commands to start our Laravel application and expose it to the internet:

    $ php artisan serve 

Take note of the port the application is currently running on (usually `8000`) after running the above command. Now open another instance of your terminal and run this command:

    $ ngrok http 8000 

After successful execution of the above command, you should see a screen like this:

![](https://paper-attachments.dropbox.com/s_F7BA2EF37979C4BF44B5AA1B9207D8D3EC9EDDE27FB9D710DDC99DD2BCB47338_1560672098731_Screenshot+from+2019-06-16+08-57-28.png)


Take note of the `forwarding` url as we will be making use of it next.

### Updating Twilio phone number configuration
Next, we will update our webhook url for our phone number SMS configuration to enable Twilio connect with our application when an SMS message is received. Head over to the [active phone number](https://www.twilio.com/console/phone-numbers/incoming) section in your Twilio console and select a active phone number from the list which will be used as the phone number for receiving messages. Scroll down to the Messaging segment and update the webhook url for the field labeled “A message comes in” as shown below:

![](https://paper-attachments.dropbox.com/s_F7BA2EF37979C4BF44B5AA1B9207D8D3EC9EDDE27FB9D710DDC99DD2BCB47338_1560674477147_Group+3.png)
 
## Testing

Great! We have completed our logic for product verification and registered our webhook. Now let’s proceed to the final stage where we test our application. 

### Testing Application
Now that we have both our application running and exposed to the web, let’s carry out the final test. To do this, simply send a text message to your active Twilio number with any of the `product_id` and wait for a response. If all goes well you should receive a response like below depending on what `product_id` you sent.
 

![](https://paper-attachments.dropbox.com/s_F7BA2EF37979C4BF44B5AA1B9207D8D3EC9EDDE27FB9D710DDC99DD2BCB47338_1560675456161_Group+4+1.png)

## Conclusion

At this point you should have a working SMS based product verification service up and running. And with that, you have also learned how to make use of Laravel to accomplish this using Twilio’s programmable SMS and also how to expose your local server using ngrok. If you will like to take a look at the complete source code for this tutorial, you can find it on [Github](https://github.com/thecodearcher/Product-verification-application-with-laravel). 

You can also take this further by allowing multiple product verification via a single text.

I’d love to answer any question(s) you might have concerning this tutorial. You can reach me via:

- Email: [brian.iyoha@gmail.com](mailto:brian.iyoha@gmail.com)
- Twitter: [thecodearcher](https://twitter.com/thecodearcher)
- GitHub: [thecodearcher](https://github.com/thecodearcher)
