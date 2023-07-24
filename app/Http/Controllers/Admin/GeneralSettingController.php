<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Frontend;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;
use Image;

class GeneralSettingController extends Controller
{
    public function index()
    {
        $pageTitle = 'General Setting';
        $timezones = json_decode(file_get_contents(resource_path('views/admin/partials/timezone.json')));
        return view('admin.setting.general', compact('pageTitle', 'timezones'));
    }

    public function update(Request $request)
    {
        $general = gs();
        $referValidation = 'nullable';
        if ($general->referral_system) {
            $referValidation = 'required';
        }

        $request->validate([
            'site_name'           => 'required|string|max:40',
            'cur_text'            => 'required|string|max:40',
            'cur_sym'             => 'required|string|max:40',
            'base_color'          => 'nullable', 'regex:/^[a-f0-9]{6}$/i',
            'timezone'            => 'required',
            'referral_commission' => $referValidation . '|numeric|gt:0',
            'upload_limit'        => 'required|numeric|gte: -1',
            'per_download'        => 'required|numeric|gte: 0'
        ]);

        $general->site_name = $request->site_name;
        $general->cur_text = $request->cur_text;
        $general->cur_sym = $request->cur_sym;
        $general->base_color = $request->base_color;
        $general->upload_limit = $request->upload_limit;
        $general->referral_commission = $request->referral_commission;
        $general->per_download = $request->per_download;
        $general->save();

        $timezoneFile = config_path('timezone.php');
        $content = '<?php $timezone = ' . $request->timezone . ' ?>';
        file_put_contents($timezoneFile, $content);
        $notify[] = ['success', 'General setting updated successfully'];
        return back()->withNotify($notify);
    }

    public function systemConfiguration()
    {
        $pageTitle = 'System Configuration';
        return view('admin.setting.configuration', compact('pageTitle'));
    }


    public function systemConfigurationSubmit(Request $request)
    {
        $general = gs();
        $general->kv = $request->kv ? 1 : 0;
        $general->ev = $request->ev ? 1 : 0;
        $general->en = $request->en ? 1 : 0;
        $general->sv = $request->sv ? 1 : 0;
        $general->sn = $request->sn ? 1 : 0;
        $general->force_ssl = $request->force_ssl ? 1 : 0;
        $general->referral_system = $request->referral_system ? Status::ENABLE : Status::DISABLE;
        $general->secure_password = $request->secure_password ? Status::ENABLE : Status::DISABLE;
        $general->registration = $request->registration ? Status::ENABLE : Status::DISABLE;
        $general->agree = $request->agree ? Status::ENABLE : Status::DISABLE;
        $general->auto_approval = $request->auto_approval ? Status::ENABLE : Status::DISABLE;
        $general->language = $request->language ? Status::ENABLE : Status::DISABLE;
        $general->save();
        $notify[] = ['success', 'System configuration updated successfully'];
        return back()->withNotify($notify);
    }


    public function logoIcon()
    {
        $pageTitle = 'Logo & Favicon';
        return view('admin.setting.logo_icon', compact('pageTitle'));
    }

    public function logoIconUpdate(Request $request)
    {
        $request->validate([
            'logo' => ['image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
            'logo_dark' => ['image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
            'favicon' => ['image', new FileTypeValidate(['png'])],
        ]);
        if ($request->hasFile('logo')) {
            try {
                $path = getFilePath('logoIcon');
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }
                Image::make($request->logo)->save($path . '/logo.png');
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload the logo'];
                return back()->withNotify($notify);
            }
        }

        if ($request->hasFile('logo_dark')) {
            try {
                $path = getFilePath('logoIcon');
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }
                Image::make($request->logo_dark)->save($path . '/logo_dark.png');
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload the logo'];
                return back()->withNotify($notify);
            }
        }

        if ($request->hasFile('favicon')) {
            try {
                $path = getFilePath('logoIcon');
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }
                $size = explode('x', getFileSize('favicon'));
                Image::make($request->favicon)->resize($size[0], $size[1])->save($path . '/favicon.png');
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload the favicon'];
                return back()->withNotify($notify);
            }
        }
        $notify[] = ['success', 'Logo & favicon updated successfully'];
        return back()->withNotify($notify);
    }

    public function customCss()
    {
        $pageTitle = 'Custom CSS';
        $file = activeTemplate(true) . 'css/custom.css';
        $file_content = @file_get_contents($file);
        return view('admin.setting.custom_css', compact('pageTitle', 'file_content'));
    }


    public function customCssSubmit(Request $request)
    {
        $file = activeTemplate(true) . 'css/custom.css';
        if (!file_exists($file)) {
            fopen($file, "w");
        }
        file_put_contents($file, $request->css);
        $notify[] = ['success', 'CSS updated successfully'];
        return back()->withNotify($notify);
    }

    public function maintenanceMode()
    {
        $pageTitle = 'Maintenance Mode';
        $maintenance = Frontend::where('data_keys', 'maintenance.data')->firstOrFail();
        return view('admin.setting.maintenance', compact('pageTitle', 'maintenance'));
    }

    public function maintenanceModeSubmit(Request $request)
    {
        $request->validate([
            'description' => 'required'
        ]);
        $general = gs();
        $general->maintenance_mode = $request->status ? Status::ENABLE : Status::DISABLE;
        $general->save();

        $maintenance = Frontend::where('data_keys', 'maintenance.data')->firstOrFail();
        $maintenance->data_values = [
            'description' => $request->description,
        ];
        $maintenance->save();

        $notify[] = ['success', 'Maintenance mode updated successfully'];
        return back()->withNotify($notify);
    }

    public function cookie()
    {
        $pageTitle = 'GDPR Cookie';
        $cookie = Frontend::where('data_keys', 'cookie.data')->firstOrFail();
        return view('admin.setting.cookie', compact('pageTitle', 'cookie'));
    }

    public function cookieSubmit(Request $request)
    {
        $request->validate([
            'short_desc' => 'required|string|max:255',
            'description' => 'required',
        ]);
        $cookie = Frontend::where('data_keys', 'cookie.data')->firstOrFail();
        $cookie->data_values = [
            'short_desc' => $request->short_desc,
            'description' => $request->description,
            'status' => $request->status ? Status::ENABLE : Status::DISABLE,
        ];
        $cookie->save();
        $notify[] = ['success', 'Cookie policy updated successfully'];
        return back()->withNotify($notify);
    }

    public function updateWaterMark(Request $request)
    {
        $request->validate([
            'watermark' => [new FileTypeValidate(['png'])]
        ]);

        if ($request->hasFile('watermark')) {
            try {
                $path = getFilePath('watermark');
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }
                $size = explode('x', getFileSize('watermark'));
                Image::make($request->watermark)->resize($size[0], $size[1])->save($path . '/watermark.png');
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload the watermark'];
                return back()->withNotify($notify);
            }
        }

        $notify[] = ['success', 'watermark has been updated.'];
        return back()->withNotify($notify);
    }

    public function updateInstruction(Request $request)
    {
        $request->validate([
            'heading' => 'required|string',
            'instruction' => 'required',

        ]);

        $general = gs();
        $general->instruction = [
            'heading' => $request->heading,
            'instruction' => $request->instruction,

        ];

        if ($request->hasFile('txt')) {
            if ($request->txt->getClientOriginalExtension() != 'txt') {
                $notify[] = ['error', 'Only txt file accepted'];
                return back()->withNotify($notify);
            }
            $filename = 'license.txt';
            $path   = 'assets/license/';

            if ($general->file != null) {
                unlink($path . $general->file);
            }

            $request->txt->move($path, $filename);
            $general->ins_file = $filename;
        }

        $general->save();

        $notify[] = ['success', 'Instruction updated successfully'];
        return back()->withNotify($notify);
    }

    public function ftpSettings(Request $request)
    {
        $pageTitle = "Storage Settings";
        return view('admin.setting.storage', compact('pageTitle'));
    }

    public function ftpSettingsUpdate(Request $request)
    {
        $request->validate(
            [
                'storage_type' => 'in:1,2',
                'host_domain'    => 'required_if:storage_type,2|url',
                'host'           => 'required_if:storage_type,2',
                'username'       => 'required_if:storage_type,2',
                'password'       => 'required_if:storage_type,2',
                'port'           => 'required_if:storage_type,2|integer',
                'root_path'      => 'required_if:storage_type,2'

            ],
            [
                'host_domain.required_if' => ':host_domain is required when ftp storage is selected',
                'host.required_if'        => ':host is required when ftp storage is selected',
                'username.required_if'    => ':username is required when ftp storage is selected',
                'password.required_if'    => ':password is required when ftp storage is selected',
                'port.required_if'        => ':port is required when ftp storage is selected',
                'root_path.required_if'   => ':root_path is required when ftp storage is selected'

            ]
        );

        $general = gs();
        $general->storage_type = $request->storage_type;
        if ($request->storage_type == 2) {
            $general->ftp = $request->except('_token', 'storage_type');
        }
        $general->save();
        $notify[] = ['success', 'Storage setting updated successfully'];
        return back()->withNotify($notify);
    }

    public function socialiteCredentials()
    {
        $pageTitle = 'Social Login Credentials';
        return view('admin.setting.social_credential', compact('pageTitle'));
    }

    public function updateSocialiteCredentialStatus($key)
    {
        $general = gs();
        $credentials = $general->socialite_credentials;
        try {
            $credentials->$key->status = $credentials->$key->status == Status::ENABLE ? Status::DISABLE : Status::ENABLE;
        } catch (\Throwable $th) {
            abort(404);
        }

        $general->socialite_credentials = $credentials;
        $general->save();

        $notify[] = ['success', 'Status changed successfully'];
        return back()->withNotify($notify);
    }

    public function updateSocialiteCredential(Request $request, $key)
    {
        $general = gs();
        $credentials = $general->socialite_credentials;
        try {
            @$credentials->$key->client_id = $request->client_id;
            @$credentials->$key->client_secret = $request->client_secret;
        } catch (\Throwable $th) {
            abort(404);
        }
        $general->socialite_credentials = $credentials;
        $general->save();

        $notify[] = ['success', ucfirst($key) . ' credential updated successfully'];
        return back()->withNotify($notify);
    }
}
