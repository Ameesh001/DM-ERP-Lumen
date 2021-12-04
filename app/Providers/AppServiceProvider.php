<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
    
    // Custom Validations
    public function boot()
    {
        ////////////////////////////// (alphabet, whitespace) //////////////////////////////////////
        app('validator')->extend('alpha_space', function ($attribute, $value) {
            return preg_match("/^[a-zA-Z, \-'.\p{L}]+$/ui", $value);
        });
        // (alphabet, whitespace) Message
        /*app('validator')->replacer('alpha_space', function ($message, $attribute, $rule, $parameters) {
            return 'The '.$attribute.' may only contain letters and spaces.';
        });*/

        //////////////////////////////(alphabet, dot, dash, underscore, space) //////////////////////////////////////
        app('validator')->extend('custom_alpha_dash_dot', function ($attribute, $value) {
            return preg_match("/^[a-zA-Z-_ '.\p{L}]+$/ui", $value);
        });
        //(alphabet, dot, dash, underscore, space) Message //////////////////////////////////////
        /*app('validator')->replacer('custom_alpha_dash_dot', function ($message, $attribute, $rule, $parameters) {
            return 'The '.$attribute.' may only contain alphabates, dots, dashes, underscores and spaces.';
        });*/
        
        //////////////////////////////(alphabet, dot, dash, underscore) //////////////////////////////////////
        app('validator')->extend('custom_alpha_dash_dot_nosp', function ($attribute, $value) {
            return preg_match('/^[a-zA-Z-_.]+$/', $value);
        });
        //////////////////////////////(alphabet, dot, dash, underscore, numaric) //////////////////////////////////////
        app('validator')->extend('custom_alpha_num_dash_dot_nosp', function ($attribute, $value) {
            return preg_match('/^[a-zA-Z0-9-_.]+$/', $value);
        });
        
        app('validator')->extend('valid_route', function ($attribute, $value) {
            return preg_match('/^[a-zA-Z0-9-_.\/]+$/', $value);
        });
        //(alphabet, dot, dash, underscore) Message //////////////////////////////////////
        /*app('validator')->replacer('custom_alpha_dash_dot_nosp', function ($message, $attribute, $rule, $parameters) {
            return 'The '.$attribute.' may only contain alphabates, dots, dashes and underscores.';
        });*/
        
        //////////////////////////////(alphabet, numeric, dot, dash, underscore, space) //////////////////////////////////////
        app('validator')->extend('custom_alpha_num_dash_dot', function ($attribute, $value) {
            // return preg_match('/^[a-zA-Z0-9-_. ]+$/', $value);
            return preg_match("/^[[a-zA-Z0-9-_. '\p{L}]+$/ui", $value);
        });
        //(alphabet, numeric, dot, dash, underscore, space) Message
        /*app('validator')->replacer('custom_alpha_num_dash_dot', function ($message, $attribute, $rule, $parameters) {
            return 'The '.$attribute.' may only contain alphabates, numerics, dashes, dots, underscores and spaces.';
        });*/
        
        //////////////////////////////(alphabet, numeric, dot, dash, underscore, space) //////////////////////////////////////
        app('validator')->extend('custom_alpha_num_dash_dot_coma', function ($attribute, $value) {
            // return preg_match('/^[a-zA-Z0-9-_.,() ]+$/', $value);
            return preg_match("/^[[a-zA-Z0-9-_.,() '\p{L}]+$/ui", $value);
        });
        //(alphabet, numeric, dot, dash, underscore, space) Message
        /*app('validator')->replacer('custom_alpha_num_dash_dot_coma', function ($message, $attribute, $rule, $parameters) {
            return 'The '.$attribute.' may only contain alphabates, numerics, dashes, undescores, dots, brackets and spaces.';
        });*/
        
        //////////////////////////////(underscore, numeric) //////////////////////////////////////
        app('validator')->extend('num_underscore', function ($attribute, $value) {
            return preg_match('/^[0-9_]+$/', $value);
        });
        //(numeric, underscore) Message
        /*app('validator')->replacer('num_underscore', function ($message, $attribute, $rule, $parameters) {
            return 'The '.$attribute.' may only contain numerics and undescores.';
        });*/
        
    }
}
