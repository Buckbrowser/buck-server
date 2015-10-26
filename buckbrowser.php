<?php

require 'vendor/autoload.php';
require 'core/buckbrowser.php';
require 'core/Model.php';
require 'config/application.php';
require 'config/db.php';
require 'config/mail.php';

BuckBrowser::cors();

$buckbrowser_server = array(
    'User.create'                         =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'User.read'                           =>  function($params) { return BuckBrowser::load_model('User')->read($params); },
    'User.update'                         =>  function($params) { return BuckBrowser::load_model('User')->update($params); },
    'User.delete'                         =>  function($params) { return BuckBrowser::load_model('User')->delete($params); },
    'User.authenticate'                   =>  function($params) { return BuckBrowser::load_model('User')->auth($params); },
    'User.get_all_companies'              =>  function($params) { return BuckBrowser::load_model('User')->get_all_companies($params); },
    'User.switch_company'                 =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'Country.read'                        =>  function($params) { return BuckBrowser::load_model('Country')->read($params); },
    'Country.get_all'                     =>  function($params) { return BuckBrowser::load_model('Country')->get_all($params); },
    'Company.create'                      =>  function($params) { return BuckBrowser::load_model('Company')->create($params); },
    'Company.read'                        =>  function($params) { return BuckBrowser::load_model('Company')->read($params); },
    'Company.update'                      =>  function($params) { return BuckBrowser::load_model('company')->update($params); },
    'Company.delete'                      =>  function($params) { return BuckBrowser::load_model('Company')->delete($params); },
    'Company.get_all_bank_accounts'       =>  function($params) { return BuckBrowser::load_model('Company')->get_all_bank_accounts($params); },
    'Company.get_all_contacts'            =>  function($params) { return BuckBrowser::load_model('Company')->get_all_contacts($params); },
    'Company.get_all_invoices'            =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'Company.add_payment_method'          =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'BankAccount.create'                  =>  function($params) { return BuckBrowser::load_model('BankAccount')->create($params); },
    'BankAccount.read'                    =>  function($params) { return BuckBrowser::load_model('BankAccount')->read($params); },
    'BankAccount.update'                  =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'BankAccount.delete'                  =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'TaxCategory.create'                  =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'TaxCategory.read'                    =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'TaxCategory.update'                  =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'TaxCategory.delete'                  =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'TaxCategory.get_all'                 =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'Contact.create'                      =>  function($params) { return BuckBrowser::load_model('Contact')->create($params); },
    'Contact.read'                        =>  function($params) { return BuckBrowser::load_model('Contact')->read($params); },
    'Contact.update'                      =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'Contact.delete'                      =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'Contact.get_all_invoices'            =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'Invoice.create'                      =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'Invoice.read'                        =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'Invoice.update'                      =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'Invoice.delete'                      =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'Expense.create'                      =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'Expense.read'                        =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'Expense.update'                      =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'Expense.delete'                      =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'Product.create'                      =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'Product.read'                        =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'Product.update'                      =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'Product.delete'                      =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'ProductCategory.create'              =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'ProductCategory.read'                =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'ProductCategory.update'              =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'ProductCategory.delete'              =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'ProductCategory.get_all'             =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'ProductCategory.get_all_products'    =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'PaymentMethod.get_all'               =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'Payment.create'                      =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'Payment.delete'                      =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'Template.create'                     =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'Template.read'                       =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'Template.update'                     =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'Template.delete'                     =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
    'Template.get_all'                    =>  function($params) { return BuckBrowser::load_model('User')->create($params); },
);

Tivoka\Server::provide($buckbrowser_server)->dispatch();