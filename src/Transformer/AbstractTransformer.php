<?php

/*
 * This file is part of the DmytrofFractalBundle package.
 *
 * (c) Dmytro Feshchenko <dmytro.feshchenko@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Dmytrof\FractalBundle\Transformer;

use Dmytrof\FractalBundle\{Exception\TransformerException, Service\ExtensionsContainer, Transformer\Extension\ExtensionInterface};
use Doctrine\Common\Collections\{ArrayCollection, Collection};
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\{Scope, TransformerAbstract};

abstract class AbstractTransformer extends TransformerAbstract
{
    /**
     * Class (or interface) of subjects handled by the transformer
     */
    protected const SUBJECT_CLASS = null;

    /**
     * @var Collection<class-string<ExtensionInterface>, ExtensionInterface>|null
     */
    protected ?Collection $extensions = null;

    /**
     * @var array<string, callable>
     */
    protected array $includeCalls = [];

    /**
     * @var list<string>
     */
    protected array $includesToRoot = [];

    protected bool $isSetup = false;

    public function __construct(
        protected ExtensionsContainer $extensionsContainer,
    ) {
    }

    /**
     * Initiates default data
     */
    protected function init(): void
    {
        foreach ($this->getExtensions() as $extension) {
            $extension->decorateTransformer($this);
        }
    }

    /**
     * Setups the transformer (decorates it with supported extensions) once
     */
    public function setup(): static
    {
        if (!$this->isSetup) {
            $this->isSetup = true;
            $this->init();
        }

        return $this;
    }

    public function getExtensionsContainer(): ExtensionsContainer
    {
        return $this->extensionsContainer;
    }

    /**
     * Returns subject class
     *
     * @throws TransformerException when SUBJECT_CLASS is not defined
     */
    public function getSubjectClass(): string
    {
        return static::SUBJECT_CLASS ?? throw new TransformerException(sprintf('%s::SUBJECT_CLASS is not defined.', static::class));
    }

    /**
     * Checks if transformer supports subject
     */
    public function supports(mixed $subject): bool
    {
        $className = $this->getSubjectClass();

        return null === $subject || $subject instanceof $className;
    }

    /**
     * Returns extensions supporting the subject class
     *
     * @return Collection<class-string<ExtensionInterface>, ExtensionInterface>
     */
    public function getExtensions(): Collection
    {
        if (null === $this->extensions) {
            $this->extensions = new ArrayCollection();
            try {
                $reflectionClass = new \ReflectionClass($this->getSubjectClass());
            } catch (TransformerException|\ReflectionException) {
                return $this->extensions;
            }
            foreach ($this->getExtensionsContainer() as $extension) {
                if ($extension->supports($reflectionClass, $this)) {
                    $this->extensions->set($extension::class, $extension);
                }
            }
        }

        return $this->extensions;
    }

    /**
     * Adds default includes
     */
    public function addDefaultInclude(string ...$defaultIncludes): static
    {
        return $this->setDefaultIncludes(array_merge($this->getDefaultIncludes(), $defaultIncludes));
    }

    /**
     * Removes default includes
     */
    public function removeDefaultInclude(string ...$defaultIncludes): static
    {
        return $this->setDefaultIncludes(array_values(array_diff($this->getDefaultIncludes(), $defaultIncludes)));
    }

    /**
     * Resets default includes
     */
    public function resetDefaultIncludes(): static
    {
        return $this->setDefaultIncludes([]);
    }

    /**
     * Adds available includes
     */
    public function addAvailableInclude(string ...$includes): static
    {
        return $this->setAvailableIncludes(array_merge($this->getAvailableIncludes(), $includes));
    }

    /**
     * Removes available includes
     */
    public function removeAvailableInclude(string ...$includes): static
    {
        return $this->setAvailableIncludes(array_values(array_diff($this->getAvailableIncludes(), $includes)));
    }

    /**
     * Returns include calls
     *
     * @return array<string, callable>
     */
    public function getIncludeCalls(): array
    {
        return $this->includeCalls;
    }

    /**
     * Sets include calls
     *
     * @param array<string, callable> $includeCalls
     */
    public function setIncludeCalls(array $includeCalls): static
    {
        $this->includeCalls = $includeCalls;

        return $this;
    }

    /**
     * Checks if include call exists
     */
    public function hasIncludeCall(string $include): bool
    {
        return isset($this->includeCalls[$include]);
    }

    /**
     * Sets include call: callable(mixed $subject, AbstractTransformer $transformer, ParamBag $params): ?ResourceInterface
     */
    public function setIncludeCall(string $include, callable $call): static
    {
        $this->includeCalls[$include] = $call;

        return $this;
    }

    /**
     * Returns include call
     */
    public function getIncludeCall(string $include): ?callable
    {
        return $this->includeCalls[$include] ?? null;
    }

    /**
     * Removes include call
     */
    public function removeIncludeCall(string $include): static
    {
        unset($this->includeCalls[$include]);

        return $this;
    }

    /**
     * Returns includes merged to the root of transformed data
     *
     * @return list<string>
     */
    public function getIncludesToRoot(): array
    {
        return $this->includesToRoot;
    }

    /**
     * Sets includes to root
     *
     * @param list<string> $includesToRoot
     */
    public function setIncludesToRoot(array $includesToRoot): static
    {
        $this->includesToRoot = array_values($includesToRoot);

        return $this;
    }

    /**
     * Checks if include to root exists
     */
    public function hasIncludeToRoot(string $include): bool
    {
        return in_array($include, $this->includesToRoot, true);
    }

    /**
     * Adds includes to root
     */
    public function addIncludeToRoot(string ...$includes): static
    {
        return $this->setIncludesToRoot(array_merge($this->includesToRoot, $includes));
    }

    /**
     * Removes includes to root
     */
    public function removeIncludeToRoot(string ...$includes): static
    {
        return $this->setIncludesToRoot(array_diff($this->includesToRoot, $includes));
    }

    /**
     * Transforms date time
     */
    public function transformDateTime(?\DateTimeInterface $dateTime, string $format = \DateTimeInterface::ATOM): ?string
    {
        return $dateTime?->format($format);
    }

    /**
     * Transforms subject with the transformer and its extensions
     *
     * @throws TransformerException when the subject is not supported
     */
    public function transform(mixed $subject): array
    {
        $this->setup();
        if (!$this->supports($subject)) {
            throw new TransformerException(sprintf('%s transforms %s subjects only.', static::class, $this->getSubjectClass()));
        }
        if (!$subject) {
            return [];
        }

        $extensionsTransformData = [];
        foreach ($this->getExtensions() as $extension) {
            $extensionsTransformData += $extension->transform($subject, $this);
        }

        return $this->transformSubject($subject) + $extensionsTransformData;
    }

    /**
     * Calls include call registered for the include (e.g. by an extension) or include method
     *
     * @internal
     *
     * @return ResourceInterface|false
     */
    protected function callIncludeMethod(Scope $scope, string $includeName, $data)
    {
        if (!$this->hasIncludeCall($includeName)) {
            return parent::callIncludeMethod($scope, $includeName, $data);
        }

        $params = $scope->getManager()->getIncludeParams($scope->getIdentifier($includeName));

        return call_user_func($this->getIncludeCall($includeName), $data, $this, $params) ?? false;
    }

    /**
     * Transforms subject to array
     */
    abstract public function transformSubject($subject): array;
}
