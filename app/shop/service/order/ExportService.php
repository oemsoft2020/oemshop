<?php

namespace app\shop\service\order;

use app\store\model\OrderAddress as OrderAddressModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * 订单导出服务类
 */
class ExportService
{
    /**
     * 订单导出
     */
    public function orderList($list)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        //列宽
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('P')->setWidth(30);

        //设置工作表标题名称
        $sheet->setTitle('订单明细');

        $sheet->setCellValue('A1', '订单号');
        $sheet->setCellValue('B1', '商品信息');
        $sheet->setCellValue('C1', '订单总额');
        $sheet->setCellValue('D1', '优惠券抵扣');
        $sheet->setCellValue('E1', '积分抵扣');
        $sheet->setCellValue('F1', '运费金额');
        $sheet->setCellValue('G1', '后台改价');
        $sheet->setCellValue('H1', '实付款金额');
        $sheet->setCellValue('I1', '支付方式');
        $sheet->setCellValue('J1', '下单时间');
        $sheet->setCellValue('K1', '买家');
        $sheet->setCellValue('L1', '买家留言');
        $sheet->setCellValue('M1', '配送方式');
        $sheet->setCellValue('N1', '自提门店名称');
        $sheet->setCellValue('O1', '自提联系人');
        $sheet->setCellValue('P1', '自提联系电话');
        $sheet->setCellValue('Q1', '收货人姓名');
        $sheet->setCellValue('R1', '联系电话');
        $sheet->setCellValue('S1', '收货人地址');
        $sheet->setCellValue('T1', '物流公司');
        $sheet->setCellValue('U1', '物流单号');
        $sheet->setCellValue('V1', '付款状态');
        $sheet->setCellValue('W1', '付款时间');
        $sheet->setCellValue('X1', '发货状态');
        $sheet->setCellValue('Y1', '发货时间');
        $sheet->setCellValue('Z1', '收货状态');
        $sheet->setCellValue('AA1', '收货时间');
        $sheet->setCellValue('AB1', '订单状态');
        $sheet->setCellValue('AC1', '微信支付交易号');
        $sheet->setCellValue('AD1', '是否已评价');

        //填充数据
        $index = 0;
        foreach ($list as $order) {
            $address = $order['address'];
            $sheet->setCellValue('A'.($index + 2), ' '.$order['order_no']);
            $sheet->setCellValue('B'.($index + 2), $this->filterProductInfo($order));
            $sheet->setCellValue('C'.($index + 2), $order['total_price']);
            $sheet->setCellValue('D'.($index + 2), $order['coupon_money']);
            $sheet->setCellValue('E'.($index + 2), $order['points_money']);
            $sheet->setCellValue('F'.($index + 2), $order['express_price']);
            $sheet->setCellValue('G'.($index + 2), "{$order['update_price']['symbol']}{$order['update_price']['value']}");
            $sheet->setCellValue('H'.($index + 2), $order['pay_price']);
            $sheet->setCellValue('I'.($index + 2), $order['pay_type']['text']);
            $sheet->setCellValue('J'.($index + 2), $order['create_time']);
            $sheet->setCellValue('K'.($index + 2), $order['user']['nickName']);
            $sheet->setCellValue('L'.($index + 2), $order['buyer_remark']);
            $sheet->setCellValue('M'.($index + 2), $order['delivery_type']['text']);
            $sheet->setCellValue('N'.($index + 2), !empty($order['extract_store']) ? $order['extract_store']['shop_name'] : '');
            $sheet->setCellValue('O'.($index + 2), !empty($order['extract']) ? $order['extract']['linkman'] : '');
            $sheet->setCellValue('P'.($index + 2), !empty($order['extract']) ? $order['extract']['phone'] : '');
            $sheet->setCellValue('Q'.($index + 2), $order['address']['name']);
            $sheet->setCellValue('R'.($index + 2), $order['address']['phone']);
            $sheet->setCellValue('S'.($index + 2), $address ? $address->getFullAddress() : '');
            $sheet->setCellValue('T'.($index + 2), $order['express']['express_name']);
            $sheet->setCellValue('U'.($index + 2), $order['express_no']);
            $sheet->setCellValue('V'.($index + 2), $order['pay_status']['text']);
            $sheet->setCellValue('W'.($index + 2), $this->filterTime($order['pay_time']));
            $sheet->setCellValue('X'.($index + 2), $order['delivery_status']['text']);
            $sheet->setCellValue('Y'.($index + 2), $this->filterTime($order['delivery_time']));
            $sheet->setCellValue('Z'.($index + 2), $order['receipt_status']['text']);
            $sheet->setCellValue('AA'.($index + 2), $this->filterTime($order['receipt_time']));
            $sheet->setCellValue('AB'.($index + 2), $order['order_status']['text']);
            $sheet->setCellValue('AC'.($index + 2), $order['transaction_id']);
            $sheet->setCellValue('AD'.($index + 2), $order['is_comment'] ? '是' : '否');
            $index ++;
        }

        //保存文件
        $writer = new Xlsx($spreadsheet);
        $filename = iconv("UTF-8","GB2312//IGNORE", '订单'). '-' . date('YmdHis') . '.xlsx';


        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
    }

    /**
     * 格式化商品信息
     */
    private function filterProductInfo($order)
    {
        $content = '';
        foreach ($order['product'] as $key => $product) {
            $content .= ($key + 1) . ".商品名称：{$product['product_name']}\n";
            !empty($product['product_attr']) && $content .= "　商品规格：{$product['product_attr']}\n";
            $content .= "　购买数量：{$product['total_num']}\n";
            $content .= "　商品总价：{$product['total_price']}元\n\n";
        }
        return $content;
    }


    /**
     * 日期值过滤
     */
    private function filterTime($value)
    {
        if (!$value) return '';
        return date('Y-m-d H:i:s', $value);
    }

    /**
     * 商品导出
     */
    public function productExportData($list)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        //列宽
        $sheet->getColumnDimension('I')->setWidth(30);
        $sheet->getColumnDimension('J')->setWidth(30);
        $sheet->getColumnDimension('O')->setWidth(60);

        //设置工作表标题名称
        $sheet->setTitle('商品导出');

        $sheet->setCellValue('A1', '商品ID');
        $sheet->setCellValue('B1', '商品名称');
        $sheet->setCellValue('C1', '一级类目');
        $sheet->setCellValue('D1', '二级类目');
        $sheet->setCellValue('E1', '三级类目');
        $sheet->setCellValue('F1', '支付方式');
        $sheet->setCellValue('G1', '总库存');
        $sheet->setCellValue('H1', '销量');
        $sheet->setCellValue('I1', '头条价格');
        $sheet->setCellValue('J1', '审核状态');
        $sheet->setCellValue('K1', '佣金比例');
        $sheet->setCellValue('L1', '是否上频道');
        $sheet->setCellValue('M1', '是否预售');
        $sheet->setCellValue('N1', '商品类型');
        $sheet->setCellValue('O1', '创建时间');
        $sheet->setCellValue('P1', '店铺名称');
        $sheet->setCellValue('Q1', '抖音平台商品链接');

        //填充数据
        $index = 0;

        foreach ($list as $goods) {
            $time = $goods['create_time'];
            $sheet->setCellValue('A'.($index + 2), ' '.$goods['product_id']);
            $sheet->setCellValue('B'.($index + 2), $goods['product_name']);
            $sheet->setCellValue('C'.($index + 2), $goods['first_cate_name']);
            $sheet->setCellValue('D'.($index + 2), $goods['send_cate_name']);
            $sheet->setCellValue('E'.($index + 2), '');//三级类目
            $sheet->setCellValue('F'.($index + 2), '');//支付方式
            $sheet->setCellValue('G'.($index + 2), $goods['product_stock']);
            $sheet->setCellValue('H'.($index + 2), $goods['product_sales']);
            $sheet->setCellValue('I'.($index + 2), $goods['price_area']);
            $sheet->setCellValue('J'.($index + 2), $goods['product_status'] == 10 ? '商品审核通过' : '商品审核不通过');
            $sheet->setCellValue('K'.($index + 2), '');//佣金比例
            $sheet->setCellValue('L'.($index + 2), '');//是否上频道
            $sheet->setCellValue('M'.($index + 2),'');//是否预售
            $sheet->setCellValue('N'.($index + 2), '普通商品');
            $sheet->setCellValue('O'.($index + 2), $time);
            $sheet->setCellValue('P'.($index + 2), $goods['supply_name']);
            $sheet->setCellValue('Q'.($index + 2), $goods['link']);
           
            $index ++;
        }

        //保存文件
        $writer = new Xlsx($spreadsheet);
        $filename = iconv("UTF-8","GB2312//IGNORE", '商品'). '-' . date('YmdHis') . '.xlsx';


        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
    }
}