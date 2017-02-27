<?php

namespace UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;
use UserBundle\Events;
use UserBundle\Form\UserType;

/**
 * @author Wenming Tang <wenming@cshome.com>
 */
class RegistrationController extends Controller
{
    /**
     * @Route("/register", name="user_register")
     * @Method({"GET", "POST"})
     */
    public function registerAction(Request $request)
    {
        $form = $this->createForm(UserType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();

            $encodedPassword = $this->get('security.password_encoder')->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($encodedPassword);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $event = new GenericEvent($user);

            $this->get('event_dispatcher')->dispatch(Events::USER_CREATED, $event);

            return $this->redirect('/');
        }

        return $this->render('UserBundle:Registration:register.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
