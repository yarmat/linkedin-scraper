# Install

Step 1
<pre>
 git clone https://github.com/yarmat/linkedin-scraper.git ./
</pre>

Step 2
<pre>
composer install
</pre>

Step 3

Fill login and password from LinkedIn
<pre>
config/linkedin.php
</pre>

Step 4

Fill db field settings
<pre>
config/db.php
</pre>

Step 5

Rename table name
<pre>
src/Models/Company.php
</pre>
```php
<?php 
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';
```

Step 6 (Run Script)
```php
php index.php
```

![screenshot](screenshot.png)