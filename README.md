# Symfony view-bundle

The goal of the bundle is to provide a convenient way of transforming PHP objects into JSON response while using it with the API.

```
#[Route('/user/me', methods: ['GET'], priority: 1)]
#[IsGranted('ROLE_USER')]
class GetMeAction extends GetAction
{
    public function __invoke(Request $request): ViewInterface
    {
        return new UserView($this->getUser());
    }
}
```

```
use \Dev\ViewBundle\Annotation\Type;

class UserView extends BindView
{
    public Uuid $id;
    public string|null $firstName = null;
    public string|null $lastName = null;
    public int $notBoundField;
    #[Type(AnswerView::class)]
    public IterableView $answers;

    public function __construct(User $user)
    {
        parent::__construct($user);
        $this->notBoundField = $user->getNotBoundField();
    }
}

```


# Installation 

```
composer install wtorsi/view-bundle
```
