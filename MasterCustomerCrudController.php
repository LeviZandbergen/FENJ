<?php

namespace App\Http\Controllers\Admin;

use App\Country;
use App\MasterCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\MasterCustomerRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;

/**
 * Class MasterCustomerCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class MasterCustomerCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    protected $filterValues = [];

    public function setup()
    {
        $this->crud->setModel('App\MasterCustomer');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/mastercustomer');
        $this->crud->setEntityNameStrings('mastercustomer', 'master_customers');
        $this->crud->addButtonFromView('top', 'export', 'export', 'start');
        $this->crud->setHeading(trans('masterCustomer.Master customers'));
        $this->crud->setTitle(trans('masterCustomer.Master customers'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    protected function setupListOperation()
    {
        $this->crud->removeButtons(['delete', 'update', 'create']);

        $this->filter = DB::table('master_customers');
//        Filter for countries
        $this->crud->addFilter([
            'name' => 'country_code',
            'type' => 'select2_multiple',
            'label' => trans('filters.customer_table.Country'),
            'placeholder' => trans('filters.customkner_table.Pick a country'),
        ],
            function () {
                if (array_key_exists('lang_code', $this->filterValues)) {
                    $languages = json_decode($this->filterValues['lang_code']);
                    $masterCountries = [];
                    foreach ($languages as $key => $language) {
                        $languageUpper = strtoupper($language);
                        $masterCountries = array_unique(array_merge($masterCountries, MasterCustomer::where('lang_code', $languageUpper)->groupBy('country_code')->pluck('country_code', 'country_code')->toArray()));
                    }
                } else {
                    $masterCountries = MasterCustomer::groupBy('country_code')->pluck('country_code', 'country_code')->toArray();
                }
                $countryTable = Country::all()->pluck('name', 'iso2')->toArray();
                $countries = array_intersect_key($countryTable, $masterCountries);
                return
                    $countries;
            },
            function ($values) {
                $this->filterValues = array_merge($this->filterValues, ['country_code' => $values]);
            });

        //        Filter for language
        $this->crud->addFilter([
            'name' => 'lang_code',
            'type' => 'select2_multiple',
            'label' => trans('filters.customer_table.Language'),
            'placeholder' => trans('filters.customer_table.Pick a language'),
        ],
            function () {
                if (array_key_exists('country_code', $this->filterValues)) {
                    $countries = json_decode($this->filterValues['country_code']);
                    $masterLanguages = [];
                    foreach ($countries as $key => $country) {
                        $countryLower = strtolower($country);
                        $masterLanguages = array_unique(array_merge($masterLanguages, MasterCustomer::where('country_code', $countryLower)->groupBy('lang_code')->pluck('lang_code', 'lang_code')->toArray()));
                    }
                } else {
                    $masterLanguages = MasterCustomer::groupBy('lang_code')->pluck('lang_code', 'lang_code')->toArray();
                }
                $databaseCountries = Country::all()->pluck('iso3', 'iso2')->toArray();
                $upperMasterLanguages = array_change_key_case($masterLanguages, $case = CASE_UPPER);
                return
                    array_intersect_key($databaseCountries, $upperMasterLanguages);
            },
            function ($values) {
                $this->filterValues = array_merge($this->filterValues, ['lang_code' => $values]);
            });

        //        Filter for original Created
        $this->crud->addFilter([
            'name' => 'original_creation_date',
            'type' => 'date_range',
            'label' => trans('filters.customer_table.Customer created'),
        ],
            false,
            function ($value) {
                $this->filterValues = array_merge($this->filterValues, ['original_creation_date' => $value]);
            });
        //        Filter for original Updated
        $this->crud->addFilter([
            'name' => 'original_update_date',
            'type' => 'date_range',
            'label' => trans('filters.customer_table.Customer updated'),
        ],
            false,
            function ($value) {
                $this->filterValues = array_merge($this->filterValues, ['original_update_date' => $value]);
            });
        //        Filter for date of birth
        $this->crud->addFilter([
            'name' => 'date_of_birth',
            'type' => 'date_range',
            'label' => trans('filters.customer_table.Date of birth'),
            'locale' => [
                'format' => 'DD-MM-YYYY',
            ],
        ],
            false,
            function ($value) {
                $this->filterValues = array_merge($this->filterValues, ['date_of_birth' => $value]);
            });
//        Filter for Loyalty Points
        $this->crud->addFilter([
            'name' => 'loyalty_points',
            'type' => 'range',
            'label' => trans('filters.customer_table.Loyalty points'),
            'label_from' => trans('filters.customer_table.Min points'),
            'label_to' => trans('filters.customer_table.Max points'),
        ], false,
            function ($value) {
                $this->filterValues = array_merge($this->filterValues, ['loyalty_points' => $value]);
            });
//        Filter for customer title
        $this->crud->addFilter([
            'name' => 'title',
            'type' => 'text',
            'label' => trans('filters.customer_table.Title'),
        ],
            false,
            function ($value) {
                $this->filterValues = array_merge($this->filterValues, ['title' => $value]);
            });
//        Filter for postalcode
        $this->crud->addFilter([
            'name' => 'postal_code',
            'type' => 'text',
            'label' => trans('filters.customer_table.Postalcode'),
        ],
            false,
            function ($value) {
                $this->filterValues = array_merge($this->filterValues, ['postal_code' => $value]);
            });
        //        Filter for newsletter
        $this->crud->addFilter([
            'name' => 'newsletter',
            'type' => 'simple',
            'label' => trans('filters.customer_table.Subscribed to newsletter'),
        ],
            false,
            function ($value) {
                $this->filterValues = array_merge($this->filterValues, ['newsletter' => $value]);
            });
//        Filter for direct mail
        $this->crud->addFilter([
            'name' => 'direct_mail',
            'type' => 'simple',
            'label' => trans('filters.customer_table.Direct Mail'),
        ],
            false,
            function ($value) {
                $this->filterValues = array_merge($this->filterValues, ['direct_mail' => $value]);
            });

//        Get all master customers in a eloquent query
        $query = MasterCustomer::select();
        if (array_key_exists('lang_code', $this->filterValues)) {
            $languages = json_decode($this->filterValues['lang_code']);
            if (array_key_exists('country_code', $this->filterValues)) {
                $countries = json_decode($this->filterValues['country_code']);
//                Makes queries when languages and countries are selected
//                        Repeats query for every country
                foreach ($countries as $country) {
//                    Repeats query for every language
                    foreach ($languages as $language) {
                        $query->orwhere('country_code', 'LIKE', $country);
                        foreach ($this->filterValues as $filter => $filterValue) {
                            $query = \MasterCustomerHelper::master_customer_list_query($query, $filter, $filterValue);
                        }
                        $query->where('lang_code', 'LIKE', $language);
                    }
                }
            } else {
//                Repeats query for every language
                foreach ($languages as $language) {
                    $query->orwhere('lang_code', 'LIKE', $language);
                    foreach ($this->filterValues as $filter => $filterValue) {
                        $query = \MasterCustomerHelper::master_customer_list_query($query, $filter, $filterValue);
                    }
                }
            }
        } elseif (array_key_exists('country_code', $this->filterValues)) {
            $countries = json_decode($this->filterValues['country_code']);
            foreach ($countries as $country) {
                $query->orwhere('lang_code', 'LIKE', $country);
                foreach ($this->filterValues as $filter => $filterValue) {
                    $query = \MasterCustomerHelper::master_customer_list_query($query, $filter, $filterValue);
                }
            }
        } else {
            foreach ($this->filterValues as $filter => $filterValue) {
                $query = \MasterCustomerHelper::master_customer_list_query($query, $filter, $filterValue);
            }
        }
        dd($query->toSql());
        $this->crud->query = $query;
        $this->crud->addColumn([
            'name' => 'title',
            'label' => trans('masterCustomer.Title'),
            'type' => 'text',
        ]);
        $this->crud->addColumn([
            'name' => 'first_name',
            'label' => trans('masterCustomer.First name'),
            'type' => 'text',
        ]);
        $this->crud->addColumn([
            'name' => 'prefix',
            'label' => trans('masterCustomer.Prefix'),
            'type' => 'text',
        ]);
        $this->crud->addColumn([
            'name' => 'last_name',
            'label' => trans('masterCustomer.Last name'),
            'type' => 'text',
        ]);
        $this->crud->addColumn([
            'name' => 'street',
            'label' => trans('masterCustomer.Street'),
            'type' => 'text',
        ]);
        $this->crud->addColumn([
            'name' => 'house_number',
            'label' => trans('masterCustomer.House number'),
            'type' => 'text',
        ]);
        $this->crud->addColumn([
            'name' => 'postal_code',
            'label' => trans('masterCustomer.Postal code'),
            'type' => 'text',
        ]);
        $this->crud->addColumn([
            'name' => 'country_code',
            'label' => trans('masterCustomer.Country code'),
            'type' => 'text',
        ]);
        $this->crud->addColumn([
            'name' => 'email',
            'label' => trans('masterCustomer.Email'),
            'type' => 'text',
        ]);
        $this->crud->addColumn([
            'name' => 'phone_number',
            'label' => trans('masterCustomer.Phone number'),
            'type' => 'text',
        ]);
        $this->crud->addColumn([
            'name' => 'newsletter',
            'label' => trans('masterCustomer.Newsletter'),
            'type' => 'text',
        ]);
        $this->crud->addColumn([
            'name' => 'direct_mail',
            'label' => trans('masterCustomer.Direct mail'),
            'type' => 'text',
        ]);
        $this->crud->filterValues = $this->filterValues;
        return view($this->crud->getListView());

    }

    protected function setupCreateOperation()
    {
        $this->crud->setValidation(MasterCustomerRequest::class);

        // TODO: remove setFromDb() and manually define Fields
        $this->crud->setFromDb();
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function export(Request $request)
    {
        $query = MasterCustomer::select();
        if (array_key_exists('country_code', $request->all())) {
            $name = 'country_code';
            $filters = json_decode($request->all()['country_code']);
            $query = \MasterCustomerHelper::country_or_language($filters, $name, $query);
        }
        if (array_key_exists('lang_code', $request->all())) {
            $name = 'lang_code';
            $filters = json_decode($request->all()['lang_code']);
            $query = \MasterCustomerHelper::country_or_language($filters, $name, $query);
        }
        foreach ($request->all() as $filter => $filterValue) {
            $query = \MasterCustomerHelper::master_customer_list_query($query, $filter, $filterValue);
        }
        dd($query->toSql());
        $filteredData = $query->get()->toArray();
        $filename = \MasterCustomerHelper::master_customer_export($filteredData);
        return response()->download($filename, $filename)->deleteFileAfterSend(true);
    }
}