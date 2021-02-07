<?php

namespace Drupal\articles\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "articles_block",
 *   admin_label = @Translation("Articles"),
 *   category = @Translation("Articles")
 * )
 */
class ArticleListBlock extends BlockBase {

    /**
     * {@inheritdoc}
     */
    public function build() {

        $node = \Drupal::routeMatch()->getParameter('node');
        $tags = $node->field_tags->referencedEntities();
        $tags = array_column($node->field_tags->getValue(), 'target_id');

        $query = \Drupal::entityQuery('node')
            ->condition('type', 'article')
            ->condition('status', 1)
            ->condition('field_tags', $tags, 'IN')
            ->sort('created', 'DESC')->range(0, 10);
        $nids = $query->execute(); 
        $nodes = Node::loadMultiple($nids);
        $articles = [];
        if(!empty($nodes)){
            foreach($nodes as $node){
                $articles[]['node'] = $node;
            }
        }
        
        $build['content'] = [      
            '#theme' => 'article_list',
            '#articles' => $articles,      
        ];

        return $build;

    }

    /**
     * {@inheritdoc}
     */
    protected function blockAccess(AccountInterface $account) {
        $node = \Drupal::routeMatch()->getParameter('node');

        if ($node instanceof \Drupal\node\NodeInterface) {
            if ($node->getType() == 'article') {
                return AccessResult::allowed();
            }        
        }

        return AccessResult::forbidden();
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheMaxAge() {
        return 0;
    }

}
