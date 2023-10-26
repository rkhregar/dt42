<?php

declare(strict_types = 1);

namespace Drupal\dt42\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a dt42stockinfoblock block.
 *
 * @Block(
 *   id = "dt42_dt42stockinfoblock",
 *   admin_label = @Translation("dt42stockInfoBlock"),
 *   category = @Translation("Custom"),
 * )
 */
final class Dt42stockinfoblockBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs the plugin instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly ClientInterface $httpClient,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_client'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'company_symbol' => $this->t('aapl'),
      'start_date' => date('Y-m-d', time()),
      'end_date' => date('Y-m-d', time()),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $form['company_symbol'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Company Symbol (ticker)'),
      '#default_value' => $this->configuration['company_symbol'],
      '#description' => $this->t('Symbol for company stocks eg. - aapl,amzn,goog,tsla'),
      '#required' => TRUE,
    ];
    $form['start_date'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Start Date'),
      '#default_value' => $this->configuration['start_date'],
      '#description' => $this->t('Start date eg. - 2022-09-30'),
      '#required' => TRUE,
    ];
    $form['end_date'] = [
      '#type' => 'textfield',
      '#title' => $this->t('End Date'),
      '#default_value' => $this->configuration['end_date'],
      '#description' => $this->t('End date eg. - 2022-09-30'),
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    $this->configuration['company_symbol'] = $form_state->getValue('company_symbol');
    $this->configuration['start_date'] = $form_state->getValue('start_date');
    $this->configuration['end_date'] = $form_state->getValue('end_date');
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $token = '34f412d51db4046a81f4180aad2233c41df5d3b1';
    $baseUrl = 'https://api.tiingo.com/tiingo/daily/';
    $startDate = $this->configuration['start_date'];
    $endDate = $this->configuration['end_date'];
    $ticker = $this->configuration['company_symbol'];
    $api = $baseUrl . $ticker . '?token=' . $token;
    $result = $this->fetchData($api);
    $html = '';
    if (!empty($result)) {
      $html .= "<b>Stock name </b> = {$result["name"]} <br>
      <b>Stock Description </b> = {$result['description']} <br>
      <b>Start Date</b> = {$result["startDate"]} <br>
      <b>End Date</b> = {$result["endDate"]} <br>
      ";
    }
// addind delay as api is not responding is data is requested to frequently
    sleep(1);
    $api2 = $baseUrl . $ticker . '/prices?startDate=' . $startDate . '&endDate=' . $endDate . '&token=' . $token;
    $api2response = $this->fetchData($api2);
    if (!empty($api2response)) {
      $html .= "<h4>Stock Price Information</h4><table><tr>";
      foreach ($api2response[0] as $key => $value) {
        $html .= "<th>" . ucfirst($key) . "</th>";
      }
      $html .= '</tr>';
      foreach ($api2response as $value) {
        $html .= "<tr>";
        foreach ($value as $val) {
          $html .= "<td>{$val}</td>";
        }
        $html .= "</tr>";
      }
      $html .= "</table>";
    }
    $build = [
      '#markup' => $html,
      '#cache' => [
        'max-age' => 0,
      ],
    ];
    return $build;
  }

  /**
   * {@inheritDoc}
   */
  public function fetchData($api) : array {
    try {
      $response = $this->httpClient->get($api);
      return json_decode($response->getBody()->getContents(), TRUE);
    }
    catch (\Throwable $e) {
      \Drupal::logger('dt42')->error('something went wrong with api call => ' . print_r($e->getMessage(), TRUE));
    }
    return [];
  }

}
