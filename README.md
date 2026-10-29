DmytrofFractalBundle
====================

This bundle helps you to implement [Fractal by League](https://fractal.thephpleague.com) into
your Symfony application

## Requirements

* PHP 8.4 or higher
* Symfony 6.4, 7.x or 8.x
* league/fractal 0.20.1+ (0.20, 0.21)
* friendsofsymfony/rest-bundle 3.x (optional)

## Installation

### Step 1: Install the bundle

    $ composer require dmytrof/fractal-bundle

### Step 2: Enable the bundle

    <?php
    // config/bundles.php

    return [
        // ...
        Dmytrof\FractalBundle\DmytrofFractalBundle::class => ['all' => true],
    ];

Services extending `Dmytrof\FractalBundle\Transformer\AbstractTransformer` and implementing
`Dmytrof\FractalBundle\Transformer\Extension\ExtensionInterface` are registered automatically
(with autoconfiguration enabled).

## Usage

Read the official Fractal [documentation](https://fractal.thephpleague.com/) before using this bundle

### 1.Basic usage

Imagine you have some models **Article**:

    <?php

    namespace App\Model;

    class Article
    {
        private ?int $id = null;
        private ?Author $author = null;
        private ?string $title = null;
        private ?string $body = null;
        private ?\DateTimeImmutable $publishedAt = null;

        public function getId(): ?int
        {
            return $this->id;
        }

        public function getAuthor(): ?Author
        {
            return $this->author;
        }

        // ...
        // Other Getters and Setters
        // ...

        public function getPublishedAt(): ?\DateTimeImmutable
        {
            return $this->publishedAt;
        }
    }

and **Author**:

    <?php

    namespace App\Model;

    class Author
    {
        private ?int $id = null;
        private ?string $firstName = null;
        private ?string $lastName = null;
        private ?string $email = null;

        public function getId(): ?int
        {
            return $this->id;
        }

        // ...
        // Other Getters and Setters
        // ...
    }

##### Create transformers for your models

AuthorTransformer:

    <?php

    namespace App\FractalTransformer;

    use App\Model\Author;
    use Dmytrof\FractalBundle\Transformer\AbstractTransformer;

    class AuthorTransformer extends AbstractTransformer
    {
        // Model which is handled by this transformer
        protected const SUBJECT_CLASS = Author::class;

        protected bool $showShortInfo = false;

        public function isShowShortInfo(): bool
        {
            return $this->showShortInfo;
        }

        public function setShowShortInfo(bool $showShortInfo = true): static
        {
            $this->showShortInfo = $showShortInfo;

            return $this;
        }

        /**
         * @param Author $subject
         */
        public function transformSubject($subject): array
        {
            $data = [
                'id' => $subject->getId(),
                'firstName' => $subject->getFirstName(),
                'lastName' => $this->isShowShortInfo() ? mb_substr($subject->getLastName(), 0, 1).'.' : $subject->getLastName(),
            ];

            if (!$this->isShowShortInfo()) {
                $data['email'] = $subject->getEmail();
            }

            return $data;
        }
    }

and ArticleTransformer:

    <?php

    namespace App\FractalTransformer;

    use App\Model\Article;
    use Dmytrof\FractalBundle\Transformer\AbstractTransformer;
    use League\Fractal\Resource\{Item, ResourceInterface};

    class ArticleTransformer extends AbstractTransformer
    {
        // Model which is handled by this transformer
        protected const SUBJECT_CLASS = Article::class;

        // Fractal 0.20+ declares includes as typed properties
        protected array $defaultIncludes = [
            'author',
        ];

        protected array $availableIncludes = [
            'body',
        ];

        /**
         * @param Article $subject
         */
        public function transformSubject($subject): array
        {
            return [
                'id' => $subject->getId(),
                'title' => $subject->getTitle(),
                'publishedAt' => $this->transformDateTime($subject->getPublishedAt()),
            ];
        }

        public function includeAuthor(Article $article): Item
        {
            // Transformer class name: the transformer service is taken from FractalManager
            return $this->item($article->getAuthor(), AuthorTransformer::class);
        }

        public function includeBody(Article $article): ResourceInterface
        {
            return $this->primitive($article->getBody());
        }
    }

##### Update controller

    <?php

    namespace App\Controller\Api\v1;

    use App\FractalTransformer\ArticleTransformer;
    use Dmytrof\FractalBundle\Service\FractalManager;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\{JsonResponse, Request};
    use Symfony\Component\Routing\Attribute\Route;

    #[Route('/api/v1/articles')]
    class ArticleController extends AbstractController
    {
        #[Route('/{id<\d+>}', methods: ['GET'])]
        public function getOne(int $id, Request $request, FractalManager $fractalManager): JsonResponse
        {
            $model = $this->getArticleById($id); // Some method to receive model

            $fractalManager
                ->parseIncludes($request->query->getString('include'))
                ->parseExcludes($request->query->getString('exclude'))
                ->parseFieldsets($request->query->all('fieldsets'))
            ;

            return new JsonResponse([
                'data' => $fractalManager->getModelData($model, ArticleTransformer::class),
            ]);
        }
    }

`FractalManager` methods:

* `getModelData(mixed $model, $transformer): ?array` - transformed item (`null` for a `null` model)
* `getCollectionData(iterable $data, $transformer): array` - transformed collection
* `getPaginatorData(iterable $iterator, $transformer): array` - transformed collection (e.g. of a paginator)
* `getTransformer(string $transformerClass): AbstractTransformer` - transformer service
* `getTransformersForSubject(mixed $subject): Collection` - transformers supporting the subject

Transformer may be a transformer instance, a callable or a class name of a transformer service.

### 2.Transformer extensions

Extensions add data, includes and include calls to transformers of supported subjects:

    <?php

    namespace App\FractalTransformer\Extension;

    use App\Model\TimestampableInterface;
    use Dmytrof\FractalBundle\Transformer\{AbstractTransformer, Extension\AbstractExtension};
    use League\Fractal\Resource\{Primitive, ResourceInterface};

    class TimestampableExtension extends AbstractExtension
    {
        protected function _supports(\ReflectionClass $reflectionClass, AbstractTransformer $transformer): bool
        {
            return $reflectionClass->implementsInterface(TimestampableInterface::class);
        }

        public function decorateTransformer(AbstractTransformer $transformer): void
        {
            $transformer
                ->addAvailableInclude('createdAt')
                ->setIncludeCall('createdAt', $this->includeCreatedAt(...))
            ;
        }

        public function includeCreatedAt(TimestampableInterface $subject, AbstractTransformer $transformer): ResourceInterface
        {
            return new Primitive($transformer->transformDateTime($subject->getCreatedAt()));
        }

        public function transform($subject, AbstractTransformer $transformer): array
        {
            // Merged to transformed data (data of the transformer has priority)
            return [];
        }
    }

### 3.FOSRestBundle

With FOSRestBundle installed, views having `fractal` context attribute are encoded to JSON as is:

    use Dmytrof\FractalBundle\ViewHandler\FractalHandler;
    use FOS\RestBundle\Context\Context;
    use FOS\RestBundle\View\View;

    $view = View::create($fractalManager->getModelData($model, ArticleTransformer::class));
    $view->setContext((new Context())->setAttribute(FractalHandler::ATTRIBUTE, true));

## Upgrading from 1.x (PHP 8.4, Symfony 6.4+, Fractal 0.20+)

* Fractal 0.20+ declares `$availableIncludes` and `$defaultIncludes` as `array` properties:
  declare them as `protected array $availableIncludes = [...]` in your transformers.
* `AbstractTransformer::transform()` returns `array`, its properties are typed.
* `AbstractTransformer::getSubjectClass()` throws `TransformerException` when `SUBJECT_CLASS` is not defined.
* `addDefaultInclude()`, `addAvailableInclude()`, `addIncludeToRoot()` and their `remove*()` methods are variadic.
* `SimpleArraySerializer` follows Fractal 0.20 signatures; an empty item is serialized as `null` by the bundle's `Scope`.
* `FractalManager::getModelData()` returns `null` for a `null` model; the scope factory argument is optional.
* An unknown transformer class name throws `TransformerException` instead of an `Error`.
* Services are defined in `Resources/config/services.php`; `symfony/yaml` is not needed.
* Configuration root is `dmytrof_fractal` (no options).

## Tests

    $ composer install
    $ vendor/bin/phpunit
