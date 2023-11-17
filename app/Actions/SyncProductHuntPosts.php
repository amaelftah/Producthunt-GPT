<?php

namespace App\Actions;

use App\Models\Batch;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class SyncProductHuntPosts
{
    public function execute()
    {
        if (Cache::get('lock')) {
            return;
        }

        $token = env('PRODUCTHUNT_DEVELOPER_TOKEN');

        $batch = Batch::latest()->first();

        if(is_null($batch)) {
            $query = <<<GQL
            {
                posts(order: RANKING) {
                  pageInfo {
                    hasNextPage
                    endCursor
                  }
                  edges {
                    cursor
                    node {
                      id
                      description
                      votesCount
                      tagline,
                      url,
                      featuredAt,
                      createdAt,
                      topics {
                        nodes {
                          name
                        }
                      },
                      comments(order: VOTES_COUNT) {
                        nodes {
                          body,
                          votesCount
                        }
                      }
                    }
                  }
                }
              }
            GQL;
        } else {
            $query = <<<GQL
        {
            posts(order: RANKING, after: "$batch->last_post_cursor") {
              pageInfo {
                hasNextPage
                endCursor
              }
              edges {
                cursor
                node {
                  id
                  description
                  votesCount
                  tagline,
                  url,
                  featuredAt,
                  createdAt,
                  topics {
                    nodes {
                      name
                    }
                  },
                  comments(order: VOTES_COUNT) {
                    nodes {
                      body,
                      votesCount
                    }
                  }
                }
              }
            }
          }
        GQL;
        }

        $response = Http::acceptJson()
        ->withToken($token)
        ->post('https://api.producthunt.com/v2/api/graphql', [
            'query' => $query
        ]);

        if($response->json('errors')) {
            if($response->json('errors')[0]['error'] == 'rate_limit_reached') {
                Cache::put('lock', true, now()->addSeconds($response->json('errors')[0]['details']['reset_in']));
                logger()->error($response);
                return;
            }
            logger()->error($response);
        }

        DB::transaction(function () use ($response) {
            $responseData = $response->json('data');

            if($responseData && $responseData['posts']) {
                $posts = $responseData['posts'];

                if(isset($posts['pageInfo']['endCursor'])) {
                    Batch::create([
                      'last_post_cursor' => $posts['pageInfo']['endCursor']
                    ]);
                }

                collect($posts['edges'])->pluck('node')->each(function ($productHuntPost) {
                    if(Post::where('product_hunt_id', $productHuntPost['id'])->exists()) {
                        return;
                    }

                    Post::create([
                      'product_hunt_id' => $productHuntPost['id'],
                      'title' => $productHuntPost['tagline'],
                      'description' => $productHuntPost['description'],
                      'url' => $productHuntPost['url'],
                      'votes' => $productHuntPost['votesCount'],
                      'featured_at' => $productHuntPost['featuredAt'],
                      'posted_at' => $productHuntPost['createdAt'],
                      'topics' => [collect($productHuntPost['topics']['nodes'])->pluck('name')->implode(',')],
                      'comments' => $productHuntPost['comments']['nodes'],
                    ]);
                });
            }
        });
    }
}
