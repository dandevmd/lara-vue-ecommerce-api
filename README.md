<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>


This is an API made with Laravel 9.X for a potential internet store.API has the following functional:
- Authorization performed with Sanctum
- Admin user
- Email validation for registered users
- Reset password by email
- Add/Remove/Update/Get products to card (for registered users cart is stored in db for guests in cookies)
- User account management (delete account, update credentials);
- Get and store Orders which belong to user
- Full CRUD for Orders and Products (only for admin users)
- Statistic for admins (active custumers, most sold product, country with most orders, total income...)
- Delete other users or make them admins (only admin)

<pre>
Brief Explanation of SQL Table Interaction:
    User table:
        - has many Products (if admin)
        - has many Orders
        - has many CartItems
        - has one Customer (info like first,last name, DOB...)
    Customer table:
        - has one CustomeAdress
    Orders table:
        - has one OrderDetail
        - has one CustomerAdress
        - has many OrderItems
        - has one Payment 
</pre>

<pre>
Install Laravel API
    Download the project (or clone using GIT)
    Confirgure your enviroment variables (mysql and mailtrap as email server)
    Navigate to the project's root directory using terminal
    Run composer install
    Run migrations php artisan migrate
    Uncomment DatabaseSeeder.php
    Run php artisan db:seed DatabaseSeeder.php
    Start local server by executing php artisan serve
</pre>
    

  
