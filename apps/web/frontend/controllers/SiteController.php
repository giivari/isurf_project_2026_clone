<?php

namespace frontend\controllers;

use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use common\models\SensorLog;
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'signup', 'alerts', 'areas', 'monitoring'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'alerts', 'areas', 'monitoring'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
            'captcha' => [
                'class' => \yii\captcha\CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage (Dashboard).
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Displays monitoring page (Replaced Devices).
     *
     * @return mixed
     */
    public function actionMonitoring()
    {
        return $this->render('monitoring');
    }



    /**
     * Displays analytics page.
     *
     * @return mixed
     */
    public function actionAnalytics()
    {
        return $this->render('analytics');
    }

    public function actionRequestData()
    {
        return $this->render('request-data');
    }

    public function actionManageRequests()
    {
        return $this->render('manage-requests');
    }

    /**
     * Displays alerts & logs page.
     *
     * @return mixed
     */
    public function actionAlerts()
    {
        return $this->render('alerts');
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        }

        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            }

            Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Verify email address
     *
     * @param string $token
     * @throws BadRequestHttpException
     * @return yii\web\Response
     */
    public function actionVerifyEmail($token)
    {
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if (($user = $model->verifyEmail()) && Yii::$app->user->login($user)) {
            Yii::$app->session->setFlash('success', 'Your email has been confirmed!');
            return $this->goHome();
        }

        Yii::$app->session->setFlash('error', 'Sorry, we are unable to verify your account with provided token.');
        return $this->goHome();
    }

    /**
     * Resend verification email
     *
     * @return mixed
     */
    public function actionResendVerificationEmail()
    {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', 'Sorry, we are unable to resend verification email for the provided email address.');
        }

        return $this->render('resendVerificationEmail', [
            'model' => $model
        ]);
    }

    public function actionAreas() {
        return $this->render("areas");
    }

    /**
     * API ENDPOINTS UNTUK DASHBOARD
     */

    public function actionGetHistory($dataType = 'Suhu Udara', $hours = 24)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $hours = (int)$hours;
        
        // Tentukan interval grouping berdasarkan rentang waktu:
        // - 24 Jam: rata-rata per 30 menit (1800 detik)
        // - 7 Hari: rata-rata per 2 jam (7200 detik)
        // - 30 Hari: rata-rata per 12 jam (43200 detik)
        if ($hours <= 24) {
            $interval = 1800; 
        } elseif ($hours <= 168) {
            $interval = 7200; 
        } else {
            $interval = 43200; 
        }

        $limitTime = time() - ($hours * 3600);

        // Group by interval menggunakan fungsi matematika SQL sederhana
        $logs = SensorLog::find()
            ->select([
                new \yii\db\Expression("FLOOR(created_at / {$interval}) * {$interval} AS grouped_time"),
                new \yii\db\Expression("AVG(temperature) AS temperature"),
                new \yii\db\Expression("AVG(humidity) AS humidity"),
                new \yii\db\Expression("AVG(tds) AS tds"),
                new \yii\db\Expression("AVG(ph) AS ph")
            ])
            ->where(['>=', 'created_at', $limitTime])
            ->groupBy(['grouped_time'])
            ->orderBy(['grouped_time' => SORT_ASC])
            ->asArray()
            ->all();

        $result = [];
        foreach ($logs as $log) {
            $val = null;
            if ($dataType === 'Suhu Udara') $val = $log['temperature'];
            if ($dataType === 'Kelembaban Udara') $val = $log['humidity'];
            if ($dataType === 'TDS Air') $val = $log['tds'];
            if ($dataType === 'pH Air') $val = $log['ph'];

            if ($val !== null) {
                $timestamp = (int)$log['grouped_time'];
                $result[] = [
                    'timestamp' => gmdate("Y-m-d\TH:i:s", $timestamp),
                    'avg_value' => round((float)$val, 2), // Bulatkan 2 desimal
                    'data_type' => $dataType
                ];
            }
        }
        return $result;
    }

    public function actionLatestReadings()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $latest = SensorLog::find()->orderBy(['created_at' => SORT_DESC])->one();
        
        if (!$latest) return [];

        return [
            ['data_type' => 'Suhu Udara', 'avg_value' => $latest->temperature ?? 0, 'timestamp' => gmdate("Y-m-d\TH:i:s", $latest->created_at)],
            ['data_type' => 'Kelembaban Udara', 'avg_value' => $latest->humidity ?? 0, 'timestamp' => gmdate("Y-m-d\TH:i:s", $latest->created_at)],
            ['data_type' => 'TDS Air', 'avg_value' => $latest->tds ?? 0, 'timestamp' => gmdate("Y-m-d\TH:i:s", $latest->created_at)],
            ['data_type' => 'pH Air', 'avg_value' => $latest->ph ?? 0, 'timestamp' => gmdate("Y-m-d\TH:i:s", $latest->created_at)],
        ];
    }

    public function actionGetLogs($hours = 24)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $limitTime = time() - ((int)$hours * 3600);
        
        $logs = SensorLog::find()
            ->where(['>=', 'created_at', $limitTime])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(100) // Batasi 100 log terbaru agar browser tetap responsif
            ->all();

        $result = [];
        foreach ($logs as $log) {
            $tanggal = gmdate("d M Y", $log->created_at);
            $waktu = gmdate("H:i:s", $log->created_at);
            
            if ($log->temperature !== null) {
                $result[] = ['tanggal' => $tanggal, 'waktu' => $waktu, 'nama_sensor' => 'Suhu Udara', 'nilai_bacaan' => $log->temperature . ' °C', 'anomali' => '-', 'status' => 'Normal'];
            }
            if ($log->humidity !== null) {
                $result[] = ['tanggal' => $tanggal, 'waktu' => $waktu, 'nama_sensor' => 'Kelembaban Udara', 'nilai_bacaan' => $log->humidity . ' %', 'anomali' => '-', 'status' => 'Normal'];
            }
            if ($log->tds !== null) {
                $result[] = ['tanggal' => $tanggal, 'waktu' => $waktu, 'nama_sensor' => 'TDS Air', 'nilai_bacaan' => $log->tds . ' ppm', 'anomali' => '-', 'status' => 'Normal'];
            }
            if ($log->ph !== null) {
                $result[] = ['tanggal' => $tanggal, 'waktu' => $waktu, 'nama_sensor' => 'pH Air', 'nilai_bacaan' => $log->ph, 'anomali' => '-', 'status' => 'Normal'];
            }
        }
        
        return $result;
    }

    public function actionExportCsv($start = null, $end = null)
    {
        $query = SensorLog::find()->orderBy(['created_at' => SORT_DESC]);
        
        if ($start) {
            $query->andWhere(['>=', 'created_at', strtotime($start)]);
        }
        if ($end) {
            $query->andWhere(['<=', 'created_at', strtotime($end)]);
        }
        
        $logs = $query->limit(10000)->all();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=isurf_sensor_log_' . date('Ymd_His') . '.csv');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Waktu (UTC)', 'Device ID', 'Suhu Udara (C)', 'Kelembaban Udara (%)', 'TDS (ppm)', 'pH']);
        
        foreach ($logs as $log) {
            fputcsv($output, [
                gmdate('Y-m-d H:i:s', $log->created_at),
                $log->device_id,
                $log->temperature,
                $log->humidity,
                $log->tds,
                $log->ph
            ]);
        }
        
        fclose($output);
        exit;
    }
}
