BuckBrowser
===================
Buckbrowser will be the tool for creating, sending and paying invoices. 

----------


How to install the server
-------------

> **Note**
> You need to have a webserver **(apache)** installed with **php** and **mysql**

Clone the server to a position where there is a web root. This means that it can be accessed by the browser from anywhere. After cloning the repository we will go to the server directory.
```
git clone git@github.com:Langstra/buckbrowser.git
cd buckbrowser/server
```
Next we need some composer packages. You need composer installed, if you do not have this then you can do that by checking out [getcomposer.org][1].
```
composer install
```
Next we need to create the database. We can do this by going into phpmyadmin or any other mysql client. Upload and execute **database.sql**.

Last thing to do is to edit *server/config/db.php* and fill it with the database credentials.

  [1]: https://getcomposer.org/download/
  
# Server API

Introduction

This document describes the API, which defines the communication between the server and the client. It also determines for some situations who is responsible: the server or the client.

Usage

We use named parameters (dictionary 1.1) in this API.

Every request should contain the following:

- 'JSON-RPC'-version
- Method
- Parameters
- Request-ID

Responses contain the request-ID and either a success or error response as specified with the method. All methods can return the error codes 35964 and 36002.



Primitive parameters

token

_Pseudo random key van 32 bytes_

Tokens are specific to a user-company combination. When a user has multiple companies connected and wants to work on the other company, it needs to call User.switch\_company which returns a new token.

Tokens expire after 168 hours.

Composite parameters

create\_error

{empty\_fields, already\_exists, incorrect\_fields}

- empty\_fields array        -    Array containing the empty parameters
- already\_exists array        -    Array containing the parameters which values are already in the database
- incorrect\_fields array        -    Array containing the parameters which values are incorrect in some way which is not further defined

update\_error

{incorrect\_fields}

- incorrect\_fields array    -    Array containing the parameters which values are incorrect in some way which is not further defined



Errorcodes

- 35964                -    Method invocation faulted

- 36000                -    Not logged in
- 36001                -    Wrong permissions
- 36002                -    Something else we cannot explain
- 36003                -    Wrong login credentials
- 36004                -    Identifier not known



Functions

User

Field list

- id                -    User identifier
- username            -    Username
- password            -    User's password
- first\_name            -    User's first name
- last\_name            -    User's last name
- email                -    Email address
- language            -    User's language
- registration\_date        -    Timestamp of when the user registered
- last\_active            -    Timestamp of when the user was last active

User.create

Parameters

- username
- password
- email
- language                (optional)

On success

{token}

On error

{registration\_error}

User.read

Parameters

- token

On succes

{username, email, language, registration\_date, last\_active}

On error

{36000 | 36003}

User.update

Parameters

- token
- username                (optional)
- password                (optional)
- email                    (optional)
- language                (optional)

On success

{true}

On error

{36000 | edit\_error}

User.delete

When only a token is given, the server will send an email to the user with a verification code. If the user is the last user connected to the company, it will also notify that in the email.

When a token and matching verification code are given, the user will be deleted from the database. If the user was the last user connected to the company, the entire company and all of its data will be deleted too.

Parameters

- token
- verification\_code            (optional)

On success

{true}

On error

{36000}

User.authenticate

Parameters

- username
- password

On success

{token}

On error

{36003}

User.get\_all\_companies

Parameters

- token

On success

{{Company.id, Company.name}}

On error

{36003}

User.switch\_company

Parameters

- token
- company\_id

On success

{token}

On error

{36000 | 36003 | 36001}

Country

Field list

- id                -    Country identifier
- name                -    Country name
- locale                -    Country abbreviation, such as 'es' for 'Spain'

Country.read

Parameters

- id

On success

{name}

On error

{36004}

Country.get\_all

Parameters

None

On success

{{id, name, locale}}

On error

{}

Company

Field list

- id                -    Identifier of the company

- name                -    Name of the company
- street\_name            -    Street name of company's location
- house\_number        -    House number of company's location
- zipcode            -    Zipcode of company's location
- place\_name            -    Place name of company's location
- id\_country            -    Identifier of the country the company is located
- email                -    Email address of the company
- tax\_number            -    Tax identification number
- company\_registration\_number -     The registration number of the company
- default\_payment\_term    -    Default payment term used on invoices
- default\_invoice\_number\_prefix -    A default string used to prepend invoice numbers
- registration\_date        -    Timestamp of the registration with the API

Company.create

Parameters

- token

- name
- street\_name                (optional)
- house\_number            (optional)
- zipcode                (optional)
- place\_name                (optional)
- id\_country                (optional)
- email
- tax\_number                (optional)
- company\_registration\_number    (optional)
- default\_invoice\_number\_prefix    (optional)
- default\_payment\_term        (optional)

On success

{Company.id}

On error

{36000}

Company.read

Parameters

- token

On success

{name, street\_name, house\_number, zipcode, place\_name, id\_country, email, tax\_number, company\_registration\_number, default\_payment\_term, default\_invoice\_number\_prefix, registration\_date}

On error

{36000, 36004}

Company.update

Parameters

- token

- name                    (optional)
- street\_name                (optional)
- house\_number            (optional)
- zipcode                (optional)
- place\_name                (optional)
- id\_country                (optional)
- email                    (optional)
- tax\_number                (optional)
- company\_registration\_number    (optional)
- default\_invoice\_number\_prefix    (optional)
- default\_payment\_term        (optional)

On success

{true}

On error

{36000}

Company.delete

This will delete the company and all data related to it, such as invoices, products etc.

Notice: this will delete the current token of the users since tokens are company specific.

Parameters

- token

On success

{true}

On error

{36000 | 36001}

Company.get\_all\_bank\_accounts

Parameters

- token

On success

{{BankAccount.id, BankAccount.account\_holder, BankAccount.iban, BankAccount.bic}}

On error

{36000 | 36001}

Company.get\_all\_contacts

Parameters

- token

On success

{{Contact.id, Contact.company}}

On error

{36000 | 36001}

Company.get\_all\_invoices

Parameters

- token

On success

{{Invoice.id, Invoice.id\_contact, Invoice.invoice\_number}}

On error

{36000 | 36001 | 36004}

Company.add\_payment\_method

Parameters

- token

- id\_payment\_method

On success

{true}

On error

{36000 | 36001 | 36004}

BankAccount

Field list

- id                -    Identifier of the bank account
- id\_company            -    Identifier of the company this bank account belongs to
- account\_holder        -    Name of the owner of the account
- iban                -    International Bank Account Number
- bic                -    Business Identifier Code

BankAccount.create

Parameters

- token
- account\_holder
- iban
- bic

On success

{BankAccount.id}

On error

{36000 | 36001}

BankAccount.read

Parameters

- token
- id

On success

{account\_holder, iban, bic}

On error

{36000 | 36001 | 36004}

BankAccount.update

Parameters

- token
- id
- account\_holder            (optional)
- iban
- bic

On success

{true}

On error

{36000 | 36001 | 36004}

BankAccount.delete

Parameters

- token
- id

On success

{true}

On error

{36000 | 36001 | 36004}

TaxCategory

Field list

- id\_country            -    Identifier of the country this tax rate belongs to
- id\_company            -    Identifier of the company this tax rate belongs to
- description            -    Describes what the tax rate is used for
- percentage            -    Representation of the tax rate percentage as a double

TaxCategory.create

Parameters

- token

- id\_country                (optional)
- description
- percentage

On success

{TaxCategory.id}

On error

{36000 | 36001 | 36004}

TaxCategory.read

Parameters

- token

- id

On success

{country\_name, description, percentage}

On error

{36000 | 36004}

TaxCategory.update

Parameters

- token
- id
- id\_country                (optional)
- description                (optional)
- percentage                (optional)

On success

{true}

On error

{36000 | 36001 | 36004}

TaxCategory.delete

Parameters

- token
- id

On success

{true}

On error

{36000 | 36001 | 36004}

TaxCategory.getAll

Parameters

- token

On success

{{TaxCategory.id, TaxCategory.id, TaxCategory.description, TaxCategory.percentage}}

On error

{36000}

Contact

Field list

- id                -    Contact identifier
- company            -    Name of the company
- first\_name            -    First name of the contact person
- last\_name            -    Last name of the contact person
- street\_name            -    Street name of the company
- house\_number        -    House number
- zipcode            -    Zipcode
- place\_name            -    Place name
- id\_country            -    Id of the country
- default\_payment\_term    -    Default payment term for the contact in days
- default\_auto\_reminder    -    Boolean for automatic sending of reminders

Contact.create

Parameters

- token
- id
- company
- first\_name
- last\_name
- street\_name
- house\_number
- zipcode
- place\_name

- id\_country                (optional)
- default\_payment\_term        (optional)
- default\_auto\_reminder        (optional)

On success

{id}

On error

{36000 | 36001 | 36004}

Contact.read

Parameters

- token

- id

On success

{company, first\_name, last\_name, street\_nane, house\_number, zipcode, place\_name, id\_country, default\_payment\_term, default\_auto\_reminder}

On error

{36000 | 36004}

Contact.update

Parameters

- token

- id
- company                (optional)
- first\_name                (optional)
- last\_name                (optional)
- street\_name                (optional)
- house\_number            (optional)
- zipcode                (optional)
- place\_name                (optional)
- id\_country                (optional)
- default\_payment\_term        (optional)
- default\_auto\_reminder        (optional)

On success

{true}

On error

{36000 | 36001 | 36004}

Contact.delete

Parameters

- token

- id

On success

{true}

On error

{36000 | 36001 | 36004}

Contact.get\_all\_invoices

Parameters

- token

- id

On success

{{Invoice.id, Invoice.invoice\_number}}

On error

{36000 | 36001 | 36004}

Invoice

Field list

- id                -    Invoice identifier
- id\_company            -    Company this invoice belongs to
- id\_contact            -    Contact this invoice will be send to
- invoice\_date            -    Send timestamp of the invoice
- payment\_term            -    Term in which the invoice should be payed
- description            -    Description of the invoice
- products            -    Array of products on the invoice
- invoice\_number        -    Administrational number identifying the invoice
- paid                -    Timestamp at which the invoice was paid
- auto\_reminder            -    Whether or not id\_contact should automatically be reminded if the payment\_term expires

Invoice.create

If payment\_term is not given, the default\_payment\_term from the contact will be used.

If auto\_reminder is not given, the default\_auto\_reminder from the contact will be used.

Product must be in an array, so multiple products can be added.

Parameters

- token

- id\_contact
- payment\_term                (optional)
- description
- products                
- auto\_reminder                (optional)

On success

{Invoice.id, invoice\_number}

On error

{36000 | 36001 | 36004}

Invoice.read

Parameters

- token

- id

On success

{id\_contact, invoice\_date, payment\_term, description, products, invoice\_number, paid, auto\_reminder}

On error

{36000 | 36001 | 36004}

Invoice.update

Product must be in an array, so multiple products can be updated.

Parameters

- token

- id
- id\_contact                (optional)
- invoice\_date                (optional)
- payment\_term                (optional)
- products                (optional)
- description                (optional)
- paid                    (optional)
- auto\_reminder                (optional)

On success

{true}

On error

{36000 | 36001 | 36004}

Invoice.delete

Parameters

- token

- id

On success

{true}

On error

{36000 | 36001 | 36004}

Expense

Field list

- id                -    Expense identifier
- id\_company            -    Company this expense belongs to
- id\_contact            -    Contact this expense will be send to
- expense\_date            -    Send timestamp of the expense
- payment\_term            -    Term in which the expense should be payed
- products            -    Array of products on the expense
- description            -    Description of the expense
- payment\_reference        -    Administrational information the contact wishes to see on the banktransfer you make when paying the expense
- paid                -    Timestamp at which the expense was paid

Expense.create

Product must be in an array, so multiple products can be added.

Parameters

- token

- id\_contact
- expense\_date
- payment\_term
- products                
- description
- payment\_reference            (optional)
- paid                    (optional)

On success

{Expense.id}

On error

{36000 | 36001}

Expense.read

Parameters

- token

- id

On success

{id\_contact, expense\_date, payment\_term, products, description, payment\_reference, paid, auto\_reminder}

On error

{36000 | 36001 | 36004}

Expense.update

Product must be in an array, so multiple products can be updated.

Parameters

- token

- id
- id\_contact                (optional)
- expense\_date                (optional)
- payment\_term                (optional)
- description                (optional)
- products                (optional)
- payment\_reference            (optional)
- paid                    (optional)

On success

{true}

On error

{36000 | 36001 | 36004}

Product

Field list

- id                -    Product identifier
- id\_company            -    Company the product is used with
- amount            -    Price of the product in cents
- description            -    Description of the product
- id\_tax\_category        -    Identifier of the tax category that is applied
- id\_product\_category        -    Identifier of the product category this product belongs to

Product.create

Parameters

- token

- amount
- description
- id\_tax\_category
- id\_product\_category            (optional)

On success

{Product.id}

On error

{36000 | 36001}

Product.read

Parameters

- token
- id

On success

{amount, description, id\_tax\_category, id\_product\_category}

On error

{36000 | 36001 | 36004}

Product.update

Parameters

- token
- id

On success

{amount, description, id\_tax\_category, id\_product\_category}

On error

{36000 | 36001 | 36004}

Product.delete

Parameters

- token
- id

On success

{true}

On error

{36000 | 36001 | 36004}

ProductCategory

Field list

- id                -    Product category identifier
- name                -    Name of the product category
- id\_company            -    Identifier of the company this product category belongs to

ProductCategory.create

Parameters

- token

- name

On success

{ProductCategory.id}

On error

{36000 | 36001}

ProductCategory.read

Parameters

- token

- id

On success

{name, id\_company}

On error

{36000 | 36001 | 36004}

ProductCategory.update

Parameters

- token
- id

- name                    (optional)

On success

{true}

On error

{36000 | 36001 | 36004}

ProductCategory.delete

Parameters

- token

- id

On success

{true}

On error

{36000 | 36001 | 36004}

ProductCategory.get\_all

Parameters

- token

On success

{{name}}

On error

{36000 | 36001 | 36004}

ProductCategory.get\_all\_products

Parameters

- token

- id

On success

{{Product.id, Product.description, Product.amount, Product.id\_tax\_category}}

On error

{36000 | 36001 | 36004}

PaymentMethod

Field list

- id                -    Payment method identifier
- name                -    Name of the payment method
- method\_name            -    Methodname

PaymentMethod.get\_all

Parameters

- token

On success

{{id, name, method\_name}, {bank\_name, bank\_id}}

On error

{36000 | 36001 | 36004}

Payment

Field list

- id                -    Payment identifier
- id\_invoice            -    Invoice identifier
- payment\_id            -    Payment identifier of the payment provider
- id\_payment\_method        -    Payment method identifier

Payment.create

Parameters

- token
- id\_invoice
- id\_payment\_method
- bank\_id                (optional)

On success

{true}

On error

{36000 | 36001 | 36004}

Payment.delete

Parameters

- token
- id

On success

{true}

On error

{36000 | 36001 | 36004}

Template

Field list

- id                -    Template identifier
- name                -    Name of the template
- id\_company            -    Identifier of the company who's template it is
- content            -    The base64-encoded template

Template.create

Parameters

- token
- name
- content

On success

{true}

On error

{36000 | 36001 | 36004}

Template.read

Parameters

- token
- id

On success

{name, content}

On error

{36000 | 36001 | 36004}

Template.update

Parameters

- token                
- name                        (optional)
- content                    (optional)

On success

{true}

On error

{36000 | 36001 | 36004}

Template.delete

Parameters

- token
- id

On success

{true}

On error

{36000 | 36001 | 36004}

Template.get\_all

Parameters

- token

On success

{id, name}

On error

{36000 | 36001 | 36004}



Dictionary

1.1 Named parameters

Named parameters, or keyword arguments, is a means of using arguments that avoids the need of order. Every parameter is given a name, which ensures easy parameter use and creates more secureness on the server side.

