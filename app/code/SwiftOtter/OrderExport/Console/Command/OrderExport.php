<?php

namespace SwiftOtter\OrderExport\Console\Command;

//use SwiftOtter\OrderExport\Action\CollectOrderData;
use SwiftOtter\OrderExport\Action\ExportOrder;
use SwiftOtter\OrderExport\Model\HeaderData;
use SwiftOtter\OrderExport\Model\HeaderDataFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OrderExport extends Command
{

    const ARG_NAME_ORDER_ID = 'order-id';
    const OPT_NAME_SHIP_DATE = 'ship-date';
    const OPT_NAME_MERCHANT_NOTES = 'notes';

    private HeaderDataFactory $headerDataFactory;
    private ExportOrder $exportOrder;

//    private CollectOrderData $collectOrderData;
//        CollectOrderData $collectOrderData,
//        $this->collectOrderData = $collectOrderData;

    public function __construct(
        HeaderDataFactory $headerDataFactory,
        ExportOrder $exportOrder,
        string $name = null
    ) {
        parent::__construct($name);
        $this->headerDataFactory = $headerDataFactory;
        $this->exportOrder = $exportOrder;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('order-export:run')
        ->setDescription('Export order to ERP')
        ->addArgument(
            self::ARG_NAME_ORDER_ID,
            InputArgument::REQUIRED,
            'Order ID'
        )
        ->addOption(
            self::OPT_NAME_SHIP_DATE,
            'd',
            InputOption::VALUE_OPTIONAL,
            'Shipping date in format YYYY-MM-DD'
        )->addOption(
            self::OPT_NAME_MERCHANT_NOTES,
            null,
            InputOption::VALUE_OPTIONAL,
            'Merchant notes'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $orderId  = (int) $input->getArgument(self::ARG_NAME_ORDER_ID);
        $notes    = $input->getOption(self::OPT_NAME_MERCHANT_NOTES);
        $shipDate = $input->getOption(self::OPT_NAME_SHIP_DATE);

        /** @var HeaderData $headerData */
        $headerData = $this->headerDataFactory->create();

        if($shipDate){
            $headerData->setShipDate(new \DateTime($shipDate));
        }

        if($notes){
            $headerData->setMerchantNotes($notes);
        }

        $result = $this->exportOrder->execute($orderId, $headerData);

        $success = $result['success'] ?? null;
        if ($success){
            $output->writeln(__('Successfully exported order.'));
        }else{
            $msg = $result['error'] ?? null;
            if($msg === null){
                $msg = __('Unexpected errors occurred');
            }
            $output->writeln($msg);
            return 1;
        }
//        $orderData = $this->collectOrderData->execute($orderId, $headerData);
//        $output->writeln(print_r($orderData, true));

        return 0;
    }
}