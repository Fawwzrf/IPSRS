<?php
use App\Modules\Ipsrs\Controllers\AdminLogKerja;
use App\Modules\Ipsrs\Models\LogKerjaModel;
use App\Modules\App\Models\DbModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Request;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

<?php

namespace Tests\Unit\Modules\Ipsrs\Controllers;


class AdminLogKerjaTest extends TestCase
{
    /** @var AdminLogKerja */
    protected $controller;

    /** @var MockObject|LogKerjaModel */
    protected $modelMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock LogKerjaModel
        $this->modelMock = $this->createMock(LogKerjaModel::class);

        // Partial mock to inject mocked model
        $this->controller = $this->getMockBuilder(AdminLogKerja::class)
            ->onlyMethods(['renderView', 'save_session_search'])
            ->getMock();

        $this->controller->model = $this->modelMock;
        $this->controller->uri = '/admin/log-kerja';
        $this->controller->template = 'ipsrs::admin.pekerjaan.log_kerja.';
    }

    public function testIndexReturnsViewWithData()
    {
        $this->controller->expects($this->once())->method('save_session_search');
        $this->controller->expects($this->once())
            ->method('renderView')
            ->with(
                $this->equalTo('ipsrs::admin.pekerjaan.log_kerja.index'),
                $this->callback(function ($data) {
                    return isset($data['all_teknisi'], $data['search_act'], $data['nav_sess']);
                })
            );

        DbModel::shouldReceive('allData')
            ->once()
            ->andReturn([['id' => 1, 'nama' => 'Teknisi']]);

        Session::shouldReceive('get')->andReturn(['filter' => 'test']);
        Request::shouldReceive('input')->with('n')->andReturn('n');

        $this->controller->index();
    }

    public function testSearchRedirectsToUri()
    {
        $this->controller->expects($this->once())->method('save_session_search');
        $response = $this->controller->search();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/admin/log-kerja', $response->getTargetUrl());
    }

    public function testFormModalForCreate()
    {
        $this->modelMock->expects($this->once())->method('getAllOrderKerja')->willReturn([]);
        $this->modelMock->expects($this->once())->method('getAllTeknisi')->willReturn([]);
        $this->modelMock->expects($this->once())->method('getAllSparepart')->willReturn([]);

        $this->controller->expects($this->once())
            ->method('renderView')
            ->with(
                $this->equalTo('ipsrs::admin.pekerjaan.log_kerja.form_modal'),
                $this->callback(function ($data) {
                    return isset($data['main'], $data['log_fotos'], $data['all_order_kerja'], $data['all_teknisi'], $data['all_sparepart'], $data['form_act']);
                })
            );

        $this->controller->form_modal();
    }

    public function testFormModalForEdit()
    {
        $logKerjaId = 123;
        $this->modelMock->expects($this->once())->method('getAllOrderKerja')->willReturn([]);
        $this->modelMock->expects($this->once())->method('getAllTeknisi')->willReturn([]);
        $this->modelMock->expects($this->once())->method('getAllSparepart')->willReturn([]);
        $this->modelMock->expects($this->once())->method('getLogById')->with($logKerjaId)->willReturn(['id' => $logKerjaId]);
        $this->modelMock->expects($this->once())->method('getPhotosByLogId')->with($logKerjaId)->willReturn([]);

        $this->controller->expects($this->once())
            ->method('renderView')
            ->with(
                $this->equalTo('ipsrs::admin.pekerjaan.log_kerja.form_modal'),
                $this->callback(function ($data) use ($logKerjaId) {
                    return $data['main']['id'] === $logKerjaId && $data['form_act'] === '/admin/log-kerja/save/' . $logKerjaId;
                })
            );

        $this->controller->form_modal($logKerjaId);
    }

    public function testSaveValidationFails()
    {
        // No order_kerja_id
        $this->mockFunction('_post', []);
        $response = $this->controller->save();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Order Kerja wajib dipilih', $response->getContent());

        // No tindakan
        $this->mockFunction('_post', ['order_kerja_id' => 1]);
        $response = $this->controller->save();
        $this->assertStringContainsString('Tindakan yang dilakukan wajib diisi', $response->getContent());

        // No hasil
        $this->mockFunction('_post', ['order_kerja_id' => 1, 'tindakan' => 'tes']);
        $response = $this->controller->save();
        $this->assertStringContainsString('Hasil pekerjaan wajib dipilih', $response->getContent());
    }

    public function testSaveSuccess()
    {
        $postData = [
            'order_kerja_id' => 1,
            'tindakan' => 'tes',
            'hasil' => 'ok',
            'sparepart_id' => [2, 3],
            'jumlah' => [1, 2],
            'asset_id' => 99
        ];
        $this->mockFunction('_post', $postData);

        $this->modelMock->expects($this->once())
            ->method('saveData')
            ->with(1, $this->arrayHasKey('sparepart'))
            ->willReturn(['status' => true]);

        $response = $this->controller->save();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Laporan pekerjaan berhasil disimpan', $response->getContent());
    }

    public function testSaveFailure()
    {
        $postData = [
            'order_kerja_id' => 1,
            'tindakan' => 'tes',
            'hasil' => 'ok',
            'sparepart_id' => [],
            'jumlah' => [],
        ];
        $this->mockFunction('_post', $postData);

        $this->modelMock->expects($this->once())
            ->method('saveData')
            ->willReturn(['status' => false, 'message' => 'Gagal']);

        Log::shouldReceive('error')->once();

        $response = $this->controller->save();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Gagal', $response->getContent());
    }

    public function testAjaxDatatables()
    {
        $this->modelMock->expects($this->once())->method('loadDatatables')->willReturn(['data' => []]);
        $result = $this->controller->ajax_datatables();
        $this->assertEquals(['data' => []], $result);
    }

    public function testDetailModal()
    {
        $orderKerjaId = 5;
        $this->modelMock->expects($this->once())->method('getPenugasanByOrderKerja')->with($orderKerjaId)->willReturn(['penugasan']);
        $this->modelMock->expects($this->once())->method('getLogByOrderKerja')->with($orderKerjaId)->willReturn([
            ['log_kerja_id' => 1]
        ]);
        $this->modelMock->expects($this->once())->method('getSparepartByLogKerja')->with(1)->willReturn(['sparepart']);
        $this->modelMock->expects($this->once())->method('getPhotosByLogId')->with(1)->willReturn(['foto']);

        View::shouldReceive('make')->andReturnSelf();
        View::shouldReceive('with')->andReturnSelf();
        View::shouldReceive('render')->andReturn('view');

        $result = $this->controller->detail_modal($orderKerjaId);
        $this->assertNotEmpty($result);
    }

    // Helper to mock global functions
    protected function mockFunction($name, $return)
    {
        if (!function_exists($name)) {
            eval('function ' . $name . '() { return []; }');
        }
        // Use runkit or uopz for real global override in real test env, here just for illustration
        // In Laravel, use Facades or dependency injection for testability
        $GLOBALS['__mock_' . $name] = $return;
        runkit_function_redefine($name, '', 'return $GLOBALS["__mock_' . $name . '"];');
    }
}