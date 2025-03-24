<?php
// Archivo de prueba simple para verificar la creación de marcas, categorías y modelos

// Incluir controlador
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/controllers/InventoryController.php';

// Inicializar controlador
$inventoryController = new InventoryController();

echo "<h1>Prueba de creación de marca</h1>";
echo "<pre>";

// Intentar crear una marca
$brandData = [
    'brand_name' => 'Marca de Prueba ' . date('Ymd_His')
];

$result = $inventoryController->createBrand($brandData);
echo "Resultado de crear marca:\n";
print_r($result);
echo "\n\n";

// Intentar crear una categoría
$categoryData = [
    'category_name' => 'Categoría de Prueba ' . date('Ymd_His'),
    'description' => 'Esta es una categoría de prueba'
];

$result = $inventoryController->createCategory($categoryData);
echo "Resultado de crear categoría:\n";
print_r($result);
echo "\n\n";

// Obtener ID de marca y categoría para crear un modelo
$formOptions = $inventoryController->getFormOptions();
if (!empty($formOptions['brands']) && !empty($formOptions['categories'])) {
    $brand = $formOptions['brands'][0];
    $category = $formOptions['categories'][0];
    
    // Intentar crear un modelo
    $modelData = [
        'model_name' => 'Modelo de Prueba ' . date('Ymd_His'),
        'brand_id' => $brand['brand_id'],
        'category_id' => $category['category_id'],
        'specifications' => 'Especificaciones de prueba'
    ];
    
    $result = $inventoryController->createModel($modelData);
    echo "Resultado de crear modelo:\n";
    print_r($result);
}

echo "</pre>";
?> 