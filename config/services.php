<?php

use Kreyu\Bundle\DataTableOpenSpoutBundle\Exporter\Type\CsvExporterType;
use Kreyu\Bundle\DataTableOpenSpoutBundle\Exporter\Type\OdsExporterType;
use Kreyu\Bundle\DataTableOpenSpoutBundle\Exporter\Type\OpenSpoutExporterType;
use Kreyu\Bundle\DataTableOpenSpoutBundle\Exporter\Type\XlsxExporterType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator) {
    $configurator->services()
        ->set('kreyu_data_table_open_spout.exporter.type.open_spout', OpenSpoutExporterType::class)
        ->tag('kreyu_data_table.exporter.type')

        ->set('kreyu_data_table_open_spout.exporter.type.csv', CsvExporterType::class)
        ->tag('kreyu_data_table.exporter.type')

        ->set('kreyu_data_table_open_spout.exporter.type.xlsx', XlsxExporterType::class)
        ->tag('kreyu_data_table.exporter.type')

        ->set('kreyu_data_table_open_spout.exporter.type.ods', OdsExporterType::class)
        ->tag('kreyu_data_table.exporter.type')
    ;
};
