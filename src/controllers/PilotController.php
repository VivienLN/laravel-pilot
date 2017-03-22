<?php

namespace VivienLN\Pilot\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use VivienLN\Pilot\Pilot;
use Intervention\Image\ImageManager as ImageManager;
use Illuminate\Pagination\LengthAwarePaginator;
use VivienLN\Pilot\ModelReflectorFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PilotController extends Controller
{
    /** @var Request */
    protected $_request;
    /** @var Pilot */
    protected $_pilot;
    /** @var ModelReflectorFactory */
    protected $_reflectorFactory;

    public function __construct(Request $request, Pilot $pilot)
    {
        $this->_request = $request;
        $this->_pilot = $pilot;
        $this->_reflectorFactory = $pilot->getReflectorFactory();
    }

    /**
     * Display admin dashboard
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function index()
    {
        return view($this->_pilot->getViewName('index'), [
            'title' => 'Dashboard',
        ]);
    }

    /**
     * GET / Display all entries of $modelClass
     * @param String $slug
     * @param String $scope
     * @return string
     */
    public function table($slug, $scope = null)
    {
        $reflector = $this->_pilot->getReflectorFactory()->createFromSlug($slug);
        // User cannot list this model
        if($this->_request->user()->cant('list', $reflector['model'])) {
            abort(403, 'Unauthorized action.');
        }

        // Show only items for which the user has the "view" permission
        $modelBuilder = $reflector->getModelBuilder();
        if(!empty($scope)) {
            $items = $modelBuilder ? $modelBuilder->$scope()->get() : null ;
        } else {
            $items = $modelBuilder ? $modelBuilder->all() : null;
        }

        // eager loading (from config)
        $reflector->eagerLoad($items);

        // Load relations
        if($items && $reflector['with']) {
            // Can be array or string (if only one)
            $with = $reflector['with'];
            if(!is_array($with)) {
                $with = [$with];
            }
            // load related models
            foreach($with as $relation) {
                $items->load($relation);
            }
        }

        // Show only items for which the user has the "view" permission
        if(!empty($items)) {
            $items = $items->filter(function($item, $key) {
                return $this->_request->user()->can('view', $item);
            });
        }

        // scopes
        $scopes = [];
        if(isset($reflector['scopes']) && !empty($reflector['scopes'])) {
          foreach($reflector['scopes'] as $scopeSlug => $name) {
            $scopes[$scopeSlug] = $name;
          }
        }
    
        // Paginate after filter
        $page = $this->_request->input('page', 1);
        $items = new LengthAwarePaginator(
            $items->forPage($page, $reflector['per_page']),
            count($items),
            $reflector['per_page'],
            $page,
            // We need this so we can keep all old query parameters from the url
            [
                'path' => $this->_request->url(),
                'query' => $this->_request->query()
            ]
        );
        return view($this->_pilot->getViewName('table'), [
            'title' => $reflector['display'],
            'items' => $items,
            'slug' => $slug,
            'reflector' => $reflector,
            'scopes' => $scopes,
            'scope' => $scope,
        ]);
    }

    /**
     * GET / Display form to create a new $modelClass, or edit an existing one
     * @param String $slug
     * @param mixed $id
     * @return string
     */
    public function edit($slug, $id = null)
    {
        $reflector = $this->_pilot->getReflectorFactory()->createFromSlug($slug);
        $user = $this->_request->user();
        // user cant create new models
        if(empty($id) && $user->cant('create', $reflector['model'])) {
            abort(403, 'Unauthorized action.');
        }
        $model = $reflector->findModel($id);
        // Can the user save? (edit or create)
        if($model && $user->can('update', $model)) {
            $userAction = 'Edit';
            $canSave = true;
        } elseif(empty($model) && $user->can('create', $reflector['model'])) {
            $userAction = 'Create';
            $canSave = true;
        } else {
            $userAction = 'View';
            $canSave = false;
        }
        return view($this->_pilot->getViewName('edit'), [
            'title' => sprintf('%s: %s', $reflector['display'], $userAction),
            'model' => $model,
            'slug' => $slug,
            'reflector' => $reflector,
            'canSave' => $canSave,
        ]);
    }

    /**
     * POST / Update or create an entry of $modelClass
     * @param String $slug
     * @param mixed $id
     * @return string
     */
    public function store($slug, $id = null)
    {
        $reflector = $this->_pilot->getReflectorFactory()->createFromSlug($slug);
        $modelBuilder = $reflector->getModelBuilder();
        if(empty($id)) {
            // create model
            if($this->_request->user()->cant('create', $reflector['model'])) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            // edit model
            $model = $modelBuilder->find($id);
            if($this->_request->user()->cant('update', $model)) {
                abort(403, 'Unauthorized action.');
            }
        }
        // Validate form
        $this->validate($this->_request, $reflector->getValidationRules(), $reflector->getValidationMessages());
        // data to store
        $data = [];
        foreach($reflector['columns'] as $k => $col) {
            // $request->get() will not return default if the value is ""
            // so we must set it as null manually
            $value = $this->_request->get($k, null);
            // if $value comes from an multiple select
            // And the first (empty) value was selected,
            // it looks like this:
            // [0 => '']
            // we must change it to [] (and then, null)
            if(is_array($value)) {
                $value = array_filter($value);
            }
            // set $data
            $data[$k] = $value ? $value : null;
        }
        if(empty($model)) {
            $model = $modelBuilder::create($data);
        } else {
            $model->update($data);
        }
        // save related models
        foreach($data as $prop => $value) {
            $relation = $reflector->getRelation($model, $prop);
            if($relation) {
                if(is_a($relation, BelongsTo::class)) {
                    $relation->dissociate();
                    if(!empty($value)) {
                        $relation->associate($value);
                    }
                } else {
                    $relation->detach();
                    if(!empty($value)) {
                        $relation->attach($value);
                    }
                }
            }
        }
        $model->save();
        // redirect with success message
        $uri = sprintf('%s/%s/edit/%s', config('pilot.prefix'), $slug, $model->getKey());
        return redirect($uri)->with('status', "Item saved!");
    }

    /**
     * POST / Delete a $modelClass
     * @param Request $request
     * @param String $slug
     * @param mixed $id
     * @return string
     */
    public function delete(Request $request, Pilot $pilot, $slug, $id)
    {
        return "Delete a model of type $slug";
    }

    /**
     * @return mixed
     */
    public function upload()
    {
        // default path
        $path =  config('pilot.upload.path');

        // IF the file come from a WYSYWIG textarea
        if($this->_request->get('file_src') == 'wysywig') {

            $image = $this->_request->file('image');

            $path .= config('pilot.upload.wysywig_path');

            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }

            $file =  uniqid('image-wysywig') . '.' . config('pilot.upload.image_type');

            $imageManager = new ImageManager();

            $storedImage = $imageManager
                ->make($image)
                ->save($path . $file);

            if($storedImage) {
                return response()->json([
                    'success' => true,
                    'status' => 200,
                    'data' => [
                        'link' => url(config('pilot.upload.wysywig_path') . $file)
                    ]
                ]);
            }
        }
    }
}