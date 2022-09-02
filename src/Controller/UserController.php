<?php

namespace App\Controller;

use App\Datatables\UserDatatable;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @var DatatableFactory
     */
    private $datatableFactory;
    /**
     * @var DatatableResponse
     */
    private $datatableResponse;

    /**
     * @param DatatableFactory $datatableFactory
     * @param DatatableResponse $datatableResponse
     */
    public function __construct(DatatableFactory $datatableFactory, DatatableResponse $datatableResponse)
    {
        $this->datatableFactory = $datatableFactory;
        $this->datatableResponse = $datatableResponse;
    }

    /**
     * @Route("/", name="app_user_index", methods={"GET"})
     * @throws Exception
     */
    public function index(Request $request): Response
    {
        $isAjax = $request->isXmlHttpRequest();

        $datatable = $this->datatableFactory->create(UserDatatable::class);
        $datatable->buildDatatable();

        if ($isAjax) {

            $responseService = $this->datatableResponse;
            $responseService->setDatatable($datatable);
            $responseService->getDatatableQueryBuilder();

            return $responseService->getResponse();
        }

        return $this->render('user/index.html.twig', [
            'datatable' => $datatable,
        ]);
    }

    /**
     * @Route("/new", name="app_user_new", methods={"GET", "POST"})
     */
    public function new(Request $request, UserRepository $userRepository): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->add($user, true);

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_user_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, User $user, UserRepository $userRepository): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->add($user, true);

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_user_delete", methods={"POST"})
     */
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Bulk delete action.
     *
     * @param Request $request
     * @param UserRepository $repository
     * @param EntityManagerInterface $manager
     * @return Response
     *
     * @Route("/bulk/delete", name="app_user_bulk_delete", methods={"POST"})
     *
     */
    public function bulkDeleteAction(Request $request, UserRepository $repository, EntityManagerInterface $manager): Response
    {
        $isAjax = $request->isXmlHttpRequest();

        if ($isAjax) {
            $choices = $request->request->get('data');
            $token = $request->request->get('token');

            if (!$this->isCsrfTokenValid('multiselect', $token)) {
                throw new AccessDeniedException('The CSRF token is invalid.');
            }

            foreach ($choices as $choice) {
                $entity = $repository->find($choice['id']);
                $manager->remove($entity);
            }

            $manager->flush();

            return new Response('Success', 200);
        }

        return new Response('Bad Request', 400);
    }

    /**
     * @param Request $request
     * @param UserRepository $repository
     * @return JsonResponse|Response
     * @Route("/select2/usernames", name="select2_usernames")
     *
     */
    public function select2CreatedByUsersnames(Request $request, UserRepository $repository)
    {
        if ($request->isXmlHttpRequest()) {
            $users = $repository->findAll();

            $result = array();

            foreach ($users as $user) {
                $result[$user->getId()] = $user->getUserIdentifier();
            }

            return new JsonResponse($result);
        }

        return new Response('Bad request.', 400);
    }
}
